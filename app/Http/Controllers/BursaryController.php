<?php

namespace App\Http\Controllers;

use App\Models\FeeItem;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\StudentFeeExemption;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BursaryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeBursary($request);

        $feeItems = FeeItem::with('classes')->latest()->get();
        $activeFees = FeeItem::active()->with('classes')->get();
        $studentsQuery = User::with(['assignedClass', 'payments', 'payments.schoolClass'])
            ->whereIn('role', ['student', 'prefect']);

        if ($request->filled('class_id')) {
            $studentsQuery->where('school_class_id', $request->integer('class_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("(first_name || ' ' || last_name) like ?", ["%{$search}%"]);
            });
        }

        $students = $studentsQuery
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(30)
            ->withQueryString();

        $exemptions = StudentFeeExemption::with('feeItem')
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $students->getCollection()->transform(function (User $student) use ($activeFees, $exemptions) {
            $summary = $this->buildStudentSummary($student, $activeFees, $exemptions->get($student->id, collect()));
            $student->fee_summary = $summary;

            return $student;
        });

        return view('admin.payments.index', [
            'routePrefix' => $this->routePrefix($request),
            'feeItems' => $feeItems,
            'students' => $students,
            'classes' => SchoolClass::active()->orderBy('level')->orderBy('stream')->get(),
            'selectedClassId' => $request->query('class_id'),
            'search' => $request->query('search'),
            'totalFees' => $students->getCollection()->sum(fn ($student) => $student->fee_summary['total_due']),
            'totalPaid' => $students->getCollection()->sum(fn ($student) => $student->fee_summary['amount_paid']),
            'totalBalance' => $students->getCollection()->sum(fn ($student) => $student->fee_summary['balance']),
        ]);
    }

    public function storeFee(Request $request)
    {
        $this->authorizeBursary($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'fee_type' => ['required', Rule::in(['compulsory', 'optional'])],
            'applies_to_all_classes' => ['nullable', 'boolean'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['exists:school_classes,id'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $feeItem = FeeItem::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'amount' => $validated['amount'],
                'fee_type' => $validated['fee_type'],
                'applies_to_all_classes' => $request->boolean('applies_to_all_classes', true),
                'created_by' => $request->user()->id,
                'is_active' => true,
            ]);

            if (! $feeItem->applies_to_all_classes) {
                $feeItem->classes()->sync($validated['class_ids'] ?? []);
            }
        });

        return back()->with('success', 'Fee created successfully.');
    }

    public function showStudent(Request $request, User $student)
    {
        $this->authorizeBursary($request);
        abort_unless(in_array($student->role, ['student', 'prefect'], true), 404);

        $student->load(['assignedClass', 'payments', 'payments.schoolClass']);
        $activeFees = FeeItem::active()->with('classes')->get();
        $exemptions = StudentFeeExemption::with('feeItem')
            ->where('student_id', $student->id)
            ->get();

        return view('admin.payments.student', [
            'routePrefix' => $this->routePrefix($request),
            'student' => $student,
            'summary' => $this->buildStudentSummary($student, $activeFees, $exemptions),
        ]);
    }

    public function destroyFee(Request $request, FeeItem $feeItem)
    {
        $this->authorizeBursary($request);

        $feeItem->delete();

        return back()->with('success', 'Fee permanently deleted.');
    }

    public function toggleFee(Request $request, FeeItem $feeItem)
    {
        $this->authorizeBursary($request);

        $feeItem->update(['is_active' => ! $feeItem->is_active]);

        return back()->with('success', $feeItem->is_active ? 'Fee activated.' : 'Fee made inactive.');
    }

    public function recordPayment(Request $request, User $student)
    {
        $this->authorizeBursary($request);
        abort_unless(in_array($student->role, ['student', 'prefect'], true), 404);
        abort_unless($student->school_class_id, 422, 'Assign this student to a class before recording payment.');

        $validated = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_details' => ['nullable', 'string'],
        ]);

        $activeFees = FeeItem::active()->with('classes')->get();
        $exemptions = StudentFeeExemption::where('student_id', $student->id)->get();
        $summary = $this->buildStudentSummary($student, $activeFees, $exemptions);
        $totalDue = $summary['total_due'];
        $amountPaid = min((float) $validated['amount_paid'], $totalDue);
        $status = $totalDue <= 0 || $amountPaid >= $totalDue
            ? 'paid'
            : ($amountPaid > 0 ? 'partial' : 'unpaid');

        Payment::updateOrCreate(
            ['student_id' => $student->id, 'school_class_id' => $student->school_class_id],
            [
                'total_fees' => $totalDue,
                'amount_paid' => $amountPaid,
                'status' => $status,
                'payment_details' => $validated['payment_details'] ?? null,
                'last_payment_date' => now(),
            ]
        );

        return back()->with('success', 'Payment updated successfully.');
    }

    public function removeOptionalFee(Request $request, User $student, FeeItem $feeItem)
    {
        $this->authorizeBursary($request);
        abort_unless(in_array($student->role, ['student', 'prefect'], true), 404);
        abort_unless($feeItem->isOptional(), 422, 'Only optional fees can be removed from a student.');

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        StudentFeeExemption::firstOrCreate(
            ['student_id' => $student->id, 'fee_item_id' => $feeItem->id],
            ['removed_by' => $request->user()->id, 'reason' => $validated['reason'] ?? null]
        );

        return back()->with('success', 'Optional fee removed for this student.');
    }

    public function restoreOptionalFee(Request $request, User $student, FeeItem $feeItem)
    {
        $this->authorizeBursary($request);

        StudentFeeExemption::where('student_id', $student->id)
            ->where('fee_item_id', $feeItem->id)
            ->delete();

        return back()->with('success', 'Optional fee restored for this student.');
    }

    private function buildStudentSummary(User $student, $activeFees, $exemptions): array
    {
        $removedFeeIds = $exemptions->pluck('fee_item_id')->all();

        $applicableFees = $activeFees->filter(fn (FeeItem $fee) => $fee->appliesToStudent($student));
        $payableFees = $applicableFees->reject(fn (FeeItem $fee) => $fee->isOptional() && in_array($fee->id, $removedFeeIds, true));
        $payment = $student->payments->sortByDesc('updated_at')->first();
        $totalDue = (float) $payableFees->sum('amount');
        $amountPaid = min((float) ($payment?->amount_paid ?? 0), $totalDue);
        $balance = max($totalDue - $amountPaid, 0);

        return [
            'fees' => $payableFees,
            'optional_fees' => $applicableFees->where('fee_type', 'optional')->values(),
            'removed_fee_ids' => $removedFeeIds,
            'total_due' => $totalDue,
            'amount_paid' => $amountPaid,
            'balance' => $balance,
            'paid_percent' => $totalDue > 0 ? round(($amountPaid / $totalDue) * 100, 1) : 100,
            'unpaid_percent' => $totalDue > 0 ? round(($balance / $totalDue) * 100, 1) : 0,
            'payment' => $payment,
        ];
    }

    private function authorizeBursary(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->canAccessFinancialFeatures() || $user->can('bursary.manage')),
            403
        );
    }

    private function routePrefix(Request $request): string
    {
        $name = (string) $request->route()->getName();

        if (str_starts_with($name, 'hod.')) {
            return 'hod';
        }

        if (str_starts_with($name, 'cbt.')) {
            return 'cbt';
        }

        if (str_starts_with($name, 'teacher.')) {
            return 'teacher';
        }

        return 'admin';
    }
}
