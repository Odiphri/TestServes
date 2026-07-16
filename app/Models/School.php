<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subscription_plan_id',
        'name',
        'slug',
        'portal_url',
        'tenant_connection',
        'tenant_database',
        'tenant_database_created_at',
        'address',
        'school_type',
        'expected_students_count',
        'status',
        'subscription_status',
        'subscription_starts_at',
        'subscription_expires_at',
        'trial_ends_at',
        'subscription_ends_at',
        'payment_status',
        'next_payment_due_at',
        'payment_grace_ends_at',
        'deactivation_scheduled_at',
        'last_payment_failed_at',
        'last_payment_at',
        'activated_at',
        'auto_renew',
        'grace_period_days',
        'portal_locked',
        'portal_session_version',
        'deactivation_reason',
        'suspension_reason',
        'deactivated_at',
        'suspended_at',
        'expired_at',
        'trial_ended_at',
        'delete_scheduled_at',
        'contact_email',
        'contact_phone',
    ];

    protected function casts(): array
    {
        return [
            'subscription_starts_at' => 'date',
            'subscription_expires_at' => 'date',
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'next_payment_due_at' => 'date',
            'payment_grace_ends_at' => 'date',
            'deactivation_scheduled_at' => 'datetime',
            'last_payment_failed_at' => 'datetime',
            'last_payment_at' => 'datetime',
            'activated_at' => 'datetime',
            'auto_renew' => 'boolean',
            'portal_locked' => 'boolean',
            'portal_session_version' => 'integer',
            'deactivated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'expired_at' => 'datetime',
            'trial_ended_at' => 'datetime',
            'delete_scheduled_at' => 'datetime',
            'tenant_database_created_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function owner(): HasOne
    {
        return $this->hasOne(SchoolOwner::class)->where('is_primary', true);
    }

    public function owners(): HasMany
    {
        return $this->hasMany(SchoolOwner::class);
    }

    public function branding(): HasOne
    {
        return $this->hasOne(SchoolBrandingSetting::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SchoolSubscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentRecord::class);
    }

    public function lifecycleHistories(): HasMany
    {
        return $this->hasMany(SchoolLifecycleHistory::class);
    }

    public function hasActiveSubscription(): bool
    {
        if ($this->trashed() || $this->portal_locked || $this->status !== 'active') {
            return false;
        }

        $endsAt = $this->subscription_ends_at ?: $this->subscription_expires_at;

        return blank($endsAt) || $endsAt->endOfDay()->gte(now());
    }

    public function hasActiveTrial(): bool
    {
        return ! $this->trashed()
            && ! $this->portal_locked
            && $this->status === 'trial'
            && ($this->trial_ends_at ?: $this->subscription_expires_at)
            && ($this->trial_ends_at ?: $this->subscription_expires_at)->endOfDay()->gte(now());
    }

    public function hasPortalAccess(): bool
    {
        return $this->hasActiveSubscription() || $this->hasActiveTrial();
    }
}
