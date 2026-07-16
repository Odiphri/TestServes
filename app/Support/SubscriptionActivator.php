<?php

namespace App\Support;

use App\Models\PaymentRecord;
use Illuminate\Support\Facades\DB;

class SubscriptionActivator
{
    public function __construct(
        private readonly TenantDatabaseManager $tenants,
        private readonly SubscriptionLifecycleService $lifecycle,
    )
    {
    }

    public function activateFromPayment(PaymentRecord $payment): void
    {
        $payment->loadMissing('school');

        if (! $payment->school) {
            return;
        }

        DB::transaction(function () use ($payment) {
            $school = $payment->school()->lockForUpdate()->first();

            if (! $school) {
                return;
            }

            $school->update([
                'status' => 'active',
                'subscription_status' => 'active',
                'payment_status' => 'paid',
                'portal_locked' => false,
                'portal_session_version' => (int) ($school->portal_session_version ?: 1) + 1,
                'subscription_plan_id' => $payment->subscription_plan_id ?: $school->subscription_plan_id,
                'subscription_starts_at' => $payment->period_start ?: now()->toDateString(),
                'subscription_expires_at' => $payment->period_end ?: now()->addMonth()->toDateString(),
                'subscription_ends_at' => $payment->period_end ?: now()->addMonth()->toDateString(),
                'trial_ends_at' => null,
                'next_payment_due_at' => $payment->period_end ?: now()->addMonth()->toDateString(),
                'payment_grace_ends_at' => null,
                'deactivation_scheduled_at' => null,
                'last_payment_failed_at' => null,
                'last_payment_at' => now(),
                'activated_at' => now(),
                'deactivation_reason' => null,
                'suspension_reason' => null,
                'expired_at' => null,
                'suspended_at' => null,
                'trial_ended_at' => null,
                'deactivated_at' => null,
                'delete_scheduled_at' => null,
            ]);

            $subscriptionData = [
                'subscription_plan_id' => $payment->subscription_plan_id ?: $school->subscription_plan_id,
                'starts_at' => $payment->period_start ?: now()->toDateString(),
                'expires_at' => $payment->period_end ?: now()->addMonth()->toDateString(),
                'amount_paid' => $payment->amount,
                'billing_cycle' => $payment->period_start && $payment->period_end && $payment->period_start->diffInDays($payment->period_end) > 40 ? 'yearly' : 'monthly',
                'status' => 'active',
            ];

            $latestSubscription = $school->subscriptions()->latest()->first();
            $latestSubscription
                ? $latestSubscription->update($subscriptionData)
                : $school->subscriptions()->create($subscriptionData);
        });

        $this->lifecycle->markRenewed($payment->school->fresh());
        $this->tenants->createAndMigrate($payment->school->fresh());
    }
}
