<?php

namespace App\Support;

use App\Models\PaymentRecord;
use App\Models\School;
use App\Models\SystemSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionLifecycleService
{
    public function refresh(School $school, ?Carbon $now = null): School
    {
        $now ??= now();

        if (! $school->exists || in_array($school->status, ['deactivated', 'suspended', 'archived', 'lost'], true)) {
            return $school;
        }

        if (! $school->subscription_expires_at || $school->subscription_expires_at->endOfDay()->gte($now)) {
            return $school;
        }

        return DB::transaction(function () use ($school, $now) {
            $locked = School::query()->lockForUpdate()->find($school->id);

            if (! $locked || in_array($locked->status, ['deactivated', 'suspended', 'archived', 'lost'], true)) {
                return $school->fresh() ?: $school;
            }

            if (! $locked->subscription_expires_at || $locked->subscription_expires_at->endOfDay()->gte($now)) {
                return $locked;
            }

            $graceEnds = $locked->payment_grace_ends_at ?: $this->graceEndDate($locked->subscription_expires_at);

            if ($graceEnds->endOfDay()->lt($now)) {
                $this->deactivate($locked, 'Subscription grace period ended without a confirmed renewal payment.', $now);

                return $locked->fresh();
            }

            $locked->forceFill([
                'status' => 'expired',
                'subscription_status' => 'expired',
                'next_payment_due_at' => $locked->subscription_expires_at,
                'payment_grace_ends_at' => $graceEnds->toDateString(),
                'deactivation_scheduled_at' => $graceEnds->endOfDay(),
                'deactivation_reason' => $locked->deactivation_reason ?: 'Subscription has expired. Renew before the grace period ends to keep the school portal active.',
            ])->save();

            $this->notifyOwnerOnce(
                $locked,
                'subscription_expired_'.$locked->subscription_expires_at?->format('Ymd'),
                'Subscription expired',
                "Your TestServes subscription has expired. Please renew before {$graceEnds->format('M j, Y')} to avoid portal deactivation."
            );

            return $locked->fresh();
        });
    }

    public function deactivateForFailedPayment(PaymentRecord $payment, ?string $reason = null): void
    {
        $payment->loadMissing('school');

        if (! $payment->school) {
            return;
        }

        DB::transaction(function () use ($payment, $reason) {
            $school = School::query()->lockForUpdate()->find($payment->school_id);

            if (! $school) {
                return;
            }

            $message = $reason ?: "Payment {$payment->payment_reference} was marked as {$payment->status}. Please submit a valid renewal payment to restore the portal.";
            $this->deactivate($school, $message, now(), [
                'last_payment_failed_at' => now(),
            ]);

            $this->notifyOwnerOnce(
                $school,
                'payment_failed_'.$payment->id,
                'Payment failed',
                $message
            );
        });
    }

    public function markRenewed(School $school): void
    {
        $school->forceFill([
            'next_payment_due_at' => $school->subscription_expires_at,
            'payment_grace_ends_at' => null,
            'deactivation_scheduled_at' => null,
            'last_payment_failed_at' => null,
            'deactivation_reason' => null,
            'deactivated_at' => null,
            'delete_scheduled_at' => null,
        ])->save();

        $this->notifyOwnerOnce(
            $school,
            'subscription_renewed_'.$school->subscription_expires_at?->format('Ymd'),
            'Subscription renewed',
            'Your TestServes subscription has been renewed and your school portal is active.'
        );
    }

    public function deactivate(School $school, string $reason, ?Carbon $now = null, array $extra = []): void
    {
        $now ??= now();
        $deleteAt = $school->delete_scheduled_at ?: $now->copy()->addDays($this->deleteNoticeDays());

        $school->forceFill($extra + [
            'status' => 'deactivated',
            'subscription_status' => 'cancelled',
            'deactivation_reason' => $reason,
            'deactivated_at' => $now,
            'deactivation_scheduled_at' => $now,
            'delete_scheduled_at' => $deleteAt,
        ])->save();

        PlatformActivity::log('school_auto_deactivated', "Deactivated {$school->name}: {$reason}", $school, [
            'school_id' => $school->id,
            'new_values' => $school->only(['status', 'subscription_status', 'deactivation_reason', 'deactivated_at']),
        ]);
    }

    public function graceDays(): int
    {
        $settings = SystemSetting::values();

        return max(0, (int) ($settings['default_grace_period_days'] ?? 7));
    }

    public function deleteNoticeDays(): int
    {
        $settings = SystemSetting::values();

        return max(1, (int) ($settings['deactivated_school_delete_after_days'] ?? 30));
    }

    private function graceEndDate(Carbon $expiry): Carbon
    {
        return $expiry->copy()->addDays($this->graceDays());
    }

    private function notifyOwnerOnce(School $school, string $type, string $title, string $body): void
    {
        $owner = $school->owner()->first();

        if (! $owner) {
            return;
        }

        app(NotificationCampaignService::class)->sendSystemToOwner($owner, $type, $title, $body, $school, route('platform.payments'));
    }
}
