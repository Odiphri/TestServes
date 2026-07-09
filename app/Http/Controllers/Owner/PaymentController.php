<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PaymentRecord;
use App\Models\SystemSetting;
use App\Services\PaystackService;
use App\Support\SubscriptionActivator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index()
    {
        $owner = Auth::guard('school_owner')->user();
        $owner->load(['school.plan', 'school.payments.plan']);

        return view('owner.payments', [
            'owner' => $owner,
            'school' => $owner->school,
            'settings' => SystemSetting::values(),
            'paystackEnabled' => app(PaystackService::class)->enabled(),
            'plans' => \App\Models\SubscriptionPlan::where('status', 'active')->orderBy('monthly_price')->get(),
            'payments' => $owner->school?->payments()->with('plan')->latest()->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $owner = Auth::guard('school_owner')->user();
        $school = $owner->school?->load('plan');

        abort_unless($school, 404);

        if (! $school->subscription_plan_id || ! $school->plan) {
            return redirect()->route('platform.plans')
                ->with('error', 'Please choose a plan before submitting payment.');
        }

        $data = $request->validate([
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'payment_method' => ['required', Rule::in(['bank_transfer', 'cash', 'manual'])],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (filled($data['subscription_plan_id'] ?? null)) {
            $school->update(['subscription_plan_id' => $data['subscription_plan_id']]);
            $school->load('plan');
        }

        $plan = $school->plan;
        if (! $plan) {
            return redirect()->route('platform.payments')
                ->with('error', 'Please choose a plan before submitting payment.');
        }

        $amount = $data['billing_cycle'] === 'yearly' ? $plan->yearly_price : $plan->monthly_price;
        $periodStart = now();
        $periodEnd = $data['billing_cycle'] === 'yearly' ? now()->addYear() : now()->addMonth();

        PaymentRecord::create([
            'school_id' => $school->id,
            'school_owner_id' => $owner->id,
            'subscription_plan_id' => $plan->id,
            'amount' => $amount,
            'currency' => 'NGN',
            'payment_method' => $data['payment_method'],
            'payment_reference' => $data['payment_reference'] ?: 'OWNER-'.Str::upper(Str::random(10)),
            'status' => 'pending',
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'notes' => $data['notes'],
        ]);

        return back()->with('success', 'Payment submitted for finance review. Your portal opens after payment is confirmed.');
    }

    public function initializePaystack(Request $request, PaystackService $paystack)
    {
        $owner = Auth::guard('school_owner')->user();
        $school = $owner->school?->load('plan');

        abort_unless($school, 404);

        $data = $request->validate([
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
        ]);

        if (filled($data['subscription_plan_id'] ?? null)) {
            $school->update(['subscription_plan_id' => $data['subscription_plan_id']]);
            $school->load('plan');
        }

        if (! $school->plan) {
            return redirect()->route('platform.payments')->with('error', 'Please choose a plan before paying.');
        }

        if (! $paystack->enabled()) {
            return redirect()->route('platform.payments')->with('error', 'Paystack is not enabled yet. Use manual payment.');
        }

        $periodStart = now();
        $periodEnd = $data['billing_cycle'] === 'yearly' ? now()->addYear() : now()->addMonth();
        $payment = PaymentRecord::create([
            'school_id' => $school->id,
            'school_owner_id' => $owner->id,
            'subscription_plan_id' => $school->plan->id,
            'amount' => $data['billing_cycle'] === 'yearly' ? $school->plan->yearly_price : $school->plan->monthly_price,
            'currency' => 'NGN',
            'payment_method' => 'paystack',
            'payment_reference' => 'PSK-'.Str::upper(Str::random(14)),
            'status' => 'pending',
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'notes' => 'Paystack checkout initialized by owner.',
        ]);

        try {
            return redirect()->away($paystack->initialize($payment, $owner->email, route('platform.payments.paystack.callback')));
        } catch (\Throwable $e) {
            $payment->update(['status' => 'failed', 'notes' => $payment->notes."\nPaystack error: ".$e->getMessage()]);

            return redirect()->route('platform.payments')->with('error', $e->getMessage());
        }
    }

    public function paystackCallback(Request $request, PaystackService $paystack, SubscriptionActivator $activator)
    {
        $reference = $request->query('reference');

        if (blank($reference)) {
            return redirect()->route('platform.payments')->with('error', 'Missing Paystack reference.');
        }

        $payment = PaymentRecord::where('payment_reference', $reference)->first();

        if (! $payment) {
            return redirect()->route('platform.payments')->with('error', 'Payment reference was not found.');
        }

        try {
            $data = $paystack->verify($reference);
        } catch (\Throwable $e) {
            return redirect()->route('platform.payments')->with('error', $e->getMessage());
        }

        DB::transaction(function () use ($payment, $data, $activator) {
            if (($data['status'] ?? null) === 'success') {
                $payment->update([
                    'status' => 'paid',
                    'payment_date' => now()->toDateString(),
                    'receipt_number' => $data['id'] ?? $payment->receipt_number,
                    'notes' => trim(($payment->notes ?? '')."\nPaystack verified successfully."),
                ]);
                $activator->activateFromPayment($payment->fresh());
            } else {
                $payment->update([
                    'status' => 'failed',
                    'notes' => trim(($payment->notes ?? '')."\nPaystack verification returned: ".($data['status'] ?? 'unknown')),
                ]);
            }
        });

        return redirect()->route('platform.payments')->with('success', 'Paystack payment checked. Status: '.ucfirst($payment->fresh()->status));
    }
}
