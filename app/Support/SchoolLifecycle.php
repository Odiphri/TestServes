<?php

namespace App\Support;

use App\Models\PlatformAdmin;
use App\Models\School;
use Illuminate\Validation\ValidationException;

class SchoolLifecycle
{
    public const NEW = 'new';
    public const CONTACTED = 'contacted';
    public const INTERESTED = 'interested';
    public const TRIAL = 'trial';
    public const AWAITING_PAYMENT = 'awaiting_payment';
    public const ACTIVE = 'active';
    public const RENEWAL_DUE = 'renewal_due';
    public const EXPIRED = 'expired';
    public const SUSPENDED = 'suspended';
    public const ARCHIVED = 'archived';
    public const LOST = 'lost';

    public const LEGACY_PENDING = 'pending';
    public const DEACTIVATED = 'deactivated';

    public static function statuses(): array
    {
        return [
            self::NEW,
            self::CONTACTED,
            self::INTERESTED,
            self::TRIAL,
            self::AWAITING_PAYMENT,
            self::ACTIVE,
            self::RENEWAL_DUE,
            self::EXPIRED,
            self::SUSPENDED,
            self::DEACTIVATED,
            self::ARCHIVED,
            self::LOST,
        ];
    }

    public static function normalize(?string $status): string
    {
        return match ($status) {
            self::LEGACY_PENDING, null, '' => self::AWAITING_PAYMENT,
            default => $status,
        };
    }

    public static function transitions(): array
    {
        return [
            self::NEW => [self::CONTACTED, self::ARCHIVED, self::LOST],
            self::CONTACTED => [self::INTERESTED, self::ARCHIVED, self::LOST],
            self::INTERESTED => [self::TRIAL, self::AWAITING_PAYMENT, self::ARCHIVED, self::LOST],
            self::TRIAL => [self::AWAITING_PAYMENT, self::ACTIVE, self::EXPIRED, self::ARCHIVED],
            self::AWAITING_PAYMENT => [self::TRIAL, self::ACTIVE, self::ARCHIVED, self::LOST],
            self::ACTIVE => [self::RENEWAL_DUE, self::SUSPENDED, self::DEACTIVATED, self::ARCHIVED],
            self::RENEWAL_DUE => [self::ACTIVE, self::EXPIRED, self::SUSPENDED, self::DEACTIVATED, self::ARCHIVED],
            self::EXPIRED => [self::ACTIVE, self::SUSPENDED, self::DEACTIVATED, self::ARCHIVED],
            self::SUSPENDED => [self::ACTIVE, self::DEACTIVATED, self::ARCHIVED],
            self::DEACTIVATED => [self::ACTIVE, self::SUSPENDED, self::ARCHIVED],
            self::ARCHIVED => [self::NEW, self::CONTACTED, self::AWAITING_PAYMENT, self::ACTIVE, self::LOST],
            self::LOST => [self::CONTACTED, self::INTERESTED, self::ARCHIVED],
        ];
    }

    public function transition(School $school, string $targetStatus, ?PlatformAdmin $admin, ?string $reason = null, array $extra = []): School
    {
        $from = self::normalize($school->status);
        $to = self::normalize($targetStatus);

        if (! in_array($to, self::statuses(), true)) {
            throw ValidationException::withMessages([
                'status' => "Invalid school lifecycle status [{$targetStatus}].",
            ]);
        }

        if ($from !== $to && ! in_array($to, self::transitions()[$from] ?? [], true) && ! $admin?->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'status' => "Cannot change school from {$from} to {$to}.",
            ]);
        }

        $payload = array_merge($extra, $this->payloadForStatus($to), [
            'status' => $to,
        ]);

        $old = $school->only(array_keys($payload));
        $school->forceFill($payload)->save();

        $school->lifecycleHistories()->create([
            'previous_status' => $from,
            'new_status' => $to,
            'changed_by_admin_id' => $admin?->id,
            'changed_by_role' => $admin?->role,
            'reason' => $reason,
        ]);

        PlatformActivity::log(
            'school_lifecycle_changed',
            "Changed {$school->name} from {$from} to {$to}.",
            $school,
            [
                'school_id' => $school->id,
                'old_values' => $old,
                'new_values' => $payload,
            ]
        );

        return $school->fresh();
    }

    private function payloadForStatus(string $status): array
    {
        return match ($status) {
            self::ACTIVE => [
                'subscription_status' => 'active',
                'payment_status' => 'paid',
                'portal_locked' => false,
                'trial_ends_at' => null,
                'payment_grace_ends_at' => null,
                'deactivation_scheduled_at' => null,
                'last_payment_failed_at' => null,
                'deactivation_reason' => null,
                'deactivated_at' => null,
                'delete_scheduled_at' => null,
            ],
            self::TRIAL => [
                'subscription_status' => 'trial',
                'payment_status' => 'trial',
                'portal_locked' => false,
                'payment_grace_ends_at' => null,
                'deactivation_scheduled_at' => null,
                'last_payment_failed_at' => null,
                'deactivation_reason' => null,
                'deactivated_at' => null,
                'delete_scheduled_at' => null,
            ],
            self::EXPIRED => [
                'subscription_status' => 'expired',
                'payment_status' => 'expired',
                'portal_locked' => true,
            ],
            self::SUSPENDED => [
                'subscription_status' => 'cancelled',
                'payment_status' => 'suspended',
                'portal_locked' => true,
            ],
            self::DEACTIVATED => [
                'subscription_status' => 'cancelled',
                'payment_status' => 'deactivated',
                'portal_locked' => true,
            ],
            self::AWAITING_PAYMENT, self::NEW, self::CONTACTED, self::INTERESTED, self::LOST => [
                'subscription_status' => 'pending',
                'payment_status' => 'pending',
                'portal_locked' => true,
            ],
            default => [],
        };
    }
}
