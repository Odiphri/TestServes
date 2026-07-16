<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PaymentRecord;
use App\Models\PaymentDispute;
use App\Models\SystemSetting;
use App\Services\PaystackService;
use App\Support\SubscriptionActivator;
use App\Support\SubscriptionLifecycleService;
use App\Support\TenantDatabaseManager;
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
        $school = $owner->school ? app(SubscriptionLifecycleService::class)->refresh($owner->school) : null;

        return view('owner.payments', [
            'owner' => $owner,
            'school' => $school,
            'settings' => SystemSetting::values(),
            'paystackEnabled' => app(PaystackService::class)->enabled(),
            'plans' => \App\Models\SubscriptionPlan::where('status', 'active')->orderBy('monthly_price')->get(),
            'payments' => $school
                ? $school->payments()->with('plan')->latest()->paginate(10)
                : PaymentRecord::query()->whereRaw('1 = 0')->paginate(10),
            'hasPaidBefore' => $school?->payments()->where('status', 'paid')->exists() ?? false,
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
            'payment_evidence' => ['required_unless:payment_method,cash', 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_intent' => ['nullable', Rule::in(['new', 'renew', 'upgrade', 'downgrade'])],
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
        $evidencePath = $request->hasFile('payment_evidence')
            ? $request->file('payment_evidence')->store('payment-evidence', 'public')
            : null;

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
            'evidence_path' => $evidencePath,
            'notes' => $data['notes'],
        ]);

        return back()->with('success', 'Payment submitted for finance review. Your portal opens after payment is confirmed.');
    }

    public function destroy(PaymentRecord $payment)
    {
        $owner = Auth::guard('school_owner')->user();

        abort_unless($payment->school_owner_id === $owner->id || $payment->school_id === $owner->school?->id, 403);

        if ($payment->status === 'paid') {
            return back()->with('error', 'Paid payment records cannot be deleted from the owner portal.');
        }

        $payment->delete();

        return back()->with('success', 'Payment submission deleted.');
    }

    public function dispute(Request $request, PaymentRecord $payment)
    {
        $owner = Auth::guard('school_owner')->user();

        abort_unless($payment->school_owner_id === $owner->id || $payment->school_id === $owner->school?->id, 403);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $existing = PaymentDispute::where('payment_record_id', $payment->id)
            ->where('school_owner_id', $owner->id)
            ->whereNotIn('status', ['resolved', 'rejected'])
            ->first();

        if ($existing) {
            return back()->with('info', 'You already have an open dispute for this payment.');
        }

        PaymentDispute::create([
            'payment_record_id' => $payment->id,
            'school_id' => $payment->school_id,
            'school_owner_id' => $owner->id,
            'reference' => 'DSP-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'subject' => $data['subject'],
            'description' => $data['description'],
            'disputed_amount' => $payment->amount,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        return back()->with('success', 'Payment dispute opened. Finance will review it.');
    }

    public function startTrial(Request $request, TenantDatabaseManager $tenants)
    {
        $owner = Auth::guard('school_owner')->user();
        $school = $owner->school?->load(['plan', 'subscriptions']);

        abort_unless($school, 404);

        if (! $school->subscription_plan_id || ! $school->plan) {
            return redirect()->route('platform.plans')
                ->with('error', 'Please choose a plan before starting a free trial.');
        }

        if (in_array($school->status, ['active', 'trial'], true)) {
            return redirect()->route('platform.dashboard')
                ->with('info', 'Your portal is already open.');
        }

        $trialDays = (int) ($school->plan->trial_days ?: (SystemSetting::values()['default_trial_days'] ?? 7));
        $trialDays = max(1, $trialDays);
        $startsAt = now();
        $endsAt = now()->addDays($trialDays);

        $tenants->createAndMigrate($school);

        DB::transaction(function () use ($school, $startsAt, $endsAt) {
            $school->update([
                'status' => 'trial',
                'subscription_status' => 'trial',
                'payment_status' => 'trial',
                'portal_locked' => false,
                'subscription_starts_at' => $startsAt->toDateString(),
                'subscription_expires_at' => $endsAt->toDateString(),
                'trial_ends_at' => $endsAt,
                'subscription_ends_at' => null,
                'next_payment_due_at' => $endsAt->toDateString(),
                'payment_grace_ends_at' => null,
                'deactivation_scheduled_at' => null,
                'last_payment_failed_at' => null,
                'deactivation_reason' => null,
                'deactivated_at' => null,
                'delete_scheduled_at' => null,
            ]);

            $subscriptionData = [
                'subscription_plan_id' => $school->subscription_plan_id,
                'starts_at' => $startsAt->toDateString(),
                'expires_at' => $endsAt->toDateString(),
                'amount_paid' => 0,
                'billing_cycle' => 'trial',
                'status' => 'trial',
            ];

            $latestSubscription = $school->subscriptions()->latest()->first();
            $latestSubscription
                ? $latestSubscription->update($subscriptionData)
                : $school->subscriptions()->create($subscriptionData);
        });

        return redirect()->route('platform.dashboard')
            ->with('success', "Free trial started. Your school portal is open until {$endsAt->format('M j, Y')}.");
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
