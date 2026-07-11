<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SuperAdmin\Concerns\AuthorizesPlatformSections;
use App\Models\PaymentRecord;
use App\Models\School;
use App\Models\SubscriptionPlan;
use App\Support\PlatformActivity;
use App\Support\SubscriptionActivator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentRecordController extends Controller
{
    use AuthorizesPlatformSections;

    public function index(Request $request)
    {
        $payments = PaymentRecord::with(['school.owner', 'owner', 'plan'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('school_id'), fn ($query) => $query->where('school_id', $request->school_id))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('payment_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('payment_date', '<=', $request->to))
            ->when($request->filled('search'), fn ($query) => $query->where('payment_reference', 'like', '%'.$request->search.'%')->orWhere('receipt_number', 'like', '%'.$request->search.'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('super-admin.payments.index', [
            'payments' => $payments,
            'schools' => School::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('super-admin.payments.create', $this->formData(new PaymentRecord()));
    }

    public function store(Request $request)
    {
        $payment = PaymentRecord::create($this->validated($request));
        PlatformActivity::log('payment_created', "Created payment record {$payment->payment_reference}.", $payment);

        return redirect()->route('super-admin.payments.index')->with('success', 'Payment record created.');
    }

    public function show(PaymentRecord $payment)
    {
        $payment->load(['school.owner', 'owner', 'plan']);

        return view('super-admin.payments.show', compact('payment'));
    }

    public function edit(PaymentRecord $payment)
    {
        return view('super-admin.payments.edit', $this->formData($payment));
    }

    public function update(Request $request, PaymentRecord $payment)
    {
        $payment->update($this->validated($request));
        PlatformActivity::log('payment_updated', "Updated payment record {$payment->payment_reference}.", $payment);

        return redirect()->route('super-admin.payments.show', $payment)->with('success', 'Payment record updated.');
    }

    public function destroy(PaymentRecord $payment)
    {
        $reference = $payment->payment_reference ?: 'No reference';
        $payment->delete();
        PlatformActivity::log('payment_deleted', "Deleted payment record {$reference}.", $payment);

        return redirect()->route('super-admin.payments.index')->with('success', 'Payment record deleted.');
    }

    public function markStatus(PaymentRecord $payment, string $status, SubscriptionActivator $activator)
    {
        abort_unless(in_array($status, ['paid', 'failed', 'rejected'], true), 404);

        DB::transaction(function () use ($payment, $status, $activator) {
            $payment->update([
                'status' => $status,
                'payment_date' => $status === 'paid' ? ($payment->payment_date ?? now()->toDateString()) : $payment->payment_date,
            ]);

            if ($status === 'paid') {
                $activator->activateFromPayment($payment->fresh());
            }
        });

        PlatformActivity::log('payment_marked_'.$status, "Marked payment {$payment->payment_reference} as {$status}.", $payment);

        return back()->with('success', 'Payment status updated.');
    }

    private function formData(PaymentRecord $payment): array
    {
        return [
            'payment' => $payment,
            'schools' => School::with('owner')->orderBy('name')->get(),
            'plans' => SubscriptionPlan::orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'school_id' => ['nullable', 'exists:schools,id'],
            'school_owner_id' => ['nullable', 'exists:school_owners,id'],
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'payment_method' => ['required', Rule::in(['paystack', 'bank_transfer', 'cash', 'manual'])],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['pending', 'paid', 'failed', 'rejected', 'refunded'])],
            'payment_date' => ['nullable', 'date'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'receipt_number' => ['nullable', 'string', 'max:255'],
            'payment_evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('payment_evidence')) {
            $data['evidence_path'] = $request->file('payment_evidence')->store('payment-evidence', 'public');
        }

        unset($data['payment_evidence']);

        return $data;
    }
}
