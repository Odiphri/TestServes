<?php

namespace App\Support;

use App\Models\PaymentRecord;
use Illuminate\Support\Facades\DB;

class SubscriptionActivator
{
    public function __construct(private readonly TenantDatabaseManager $tenants)
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
                'subscription_plan_id' => $payment->subscription_plan_id ?: $school->subscription_plan_id,
                'subscription_starts_at' => $payment->period_start ?: now()->toDateString(),
                'subscription_expires_at' => $payment->period_end ?: now()->addMonth()->toDateString(),
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

        $this->tenants->createAndMigrate($payment->school->fresh());
    }
}
