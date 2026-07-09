<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PaymentDispute;
use App\Models\PaymentRecord;
use App\Models\PlatformAdmin;
use App\Models\School;
use App\Support\PlatformActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentDisputeController extends Controller
{
    public function index(Request $request)
    {
        $disputes = PaymentDispute::with(['payment', 'school', 'owner', 'assignedAdmin'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->priority))
            ->when($request->filled('school_id'), fn ($query) => $query->where('school_id', $request->school_id))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->search.'%';
                $query->where(fn ($inner) => $inner
                    ->where('reference', 'like', $search)
                    ->orWhere('subject', 'like', $search)
                    ->orWhereHas('payment', fn ($payment) => $payment->where('payment_reference', 'like', $search))
                );
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('super-admin.payment-disputes.index', [
            'disputes' => $disputes,
            'schools' => School::orderBy('name')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $dispute = new PaymentDispute();

        if ($request->filled('payment_record_id')) {
            $payment = PaymentRecord::with('school.owner')->find($request->payment_record_id);

            if ($payment) {
                $dispute->payment_record_id = $payment->id;
                $dispute->school_id = $payment->school_id;
                $dispute->school_owner_id = $payment->school_owner_id ?: $payment->school?->owner?->id;
                $dispute->disputed_amount = $payment->amount;
                $dispute->subject = 'Payment dispute for '.($payment->payment_reference ?: 'payment #'.$payment->id);
            }
        }

        return view('super-admin.payment-disputes.create', $this->formData($dispute));
    }

    public function store(Request $request)
    {
        $dispute = PaymentDispute::create($this->validated($request) + [
            'reference' => $this->reference(),
        ]);

        PlatformActivity::log('payment_dispute_created', "Created payment dispute {$dispute->reference}.", $dispute);

        return redirect()->route('super-admin.payment-disputes.show', $dispute)->with('success', 'Payment dispute opened.');
    }

    public function show(PaymentDispute $paymentDispute)
    {
        $paymentDispute->load(['payment', 'school.owner', 'owner', 'assignedAdmin']);

        return view('super-admin.payment-disputes.show', compact('paymentDispute'));
    }

    public function edit(PaymentDispute $paymentDispute)
    {
        return view('super-admin.payment-disputes.edit', $this->formData($paymentDispute));
    }

    public function update(Request $request, PaymentDispute $paymentDispute)
    {
        $paymentDispute->update($this->validated($request) + [
            'resolved_at' => in_array($request->status, ['resolved', 'rejected', 'closed'], true)
                ? ($paymentDispute->resolved_at ?? now())
                : null,
        ]);

        PlatformActivity::log('payment_dispute_updated', "Updated payment dispute {$paymentDispute->reference}.", $paymentDispute);

        return redirect()->route('super-admin.payment-disputes.show', $paymentDispute)->with('success', 'Payment dispute updated.');
    }

    public function mark(PaymentDispute $paymentDispute, string $status)
    {
        abort_unless(in_array($status, ['investigating', 'resolved', 'rejected', 'closed'], true), 404);

        $paymentDispute->update([
            'status' => $status,
            'resolved_at' => in_array($status, ['resolved', 'rejected', 'closed'], true) ? now() : null,
        ]);

        PlatformActivity::log('payment_dispute_marked_'.$status, "Marked dispute {$paymentDispute->reference} as {$status}.", $paymentDispute);

        return back()->with('success', 'Dispute status updated.');
    }

    private function formData(PaymentDispute $paymentDispute): array
    {
        return [
            'paymentDispute' => $paymentDispute,
            'payments' => PaymentRecord::with('school')->latest()->limit(200)->get(),
            'schools' => School::with('owner')->orderBy('name')->get(),
            'admins' => PlatformAdmin::whereIn('role', ['super_admin', 'finance_admin'])->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'payment_record_id' => ['nullable', 'exists:payment_records,id'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'school_owner_id' => ['nullable', 'exists:school_owners,id'],
            'assigned_admin_id' => ['nullable', 'exists:platform_admins,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'disputed_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['open', 'investigating', 'resolved', 'rejected', 'closed'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'finance_notes' => ['nullable', 'string'],
        ]);
    }

    private function reference(): string
    {
        do {
            $reference = 'DSP-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (PaymentDispute::where('reference', $reference)->exists());

        return $reference;
    }
}
