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
        'deactivation_reason',
        'deactivated_at',
        'delete_scheduled_at',
        'contact_email',
        'contact_phone',
    ];

    protected function casts(): array
    {
        return [
            'subscription_starts_at' => 'date',
            'subscription_expires_at' => 'date',
            'deactivated_at' => 'datetime',
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
        if ($this->status === 'active' && $this->subscription_status === 'active') {
            return blank($this->subscription_expires_at) || $this->subscription_expires_at->endOfDay()->gte(now());
        }

        return $this->payments()
            ->where('status', 'paid')
            ->whereNotNull('period_end')
            ->whereDate('period_end', '>=', now()->toDateString())
            ->exists();
    }

    public function hasActiveTrial(): bool
    {
        return $this->status === 'trial'
            && $this->subscription_status === 'trial'
            && $this->subscription_expires_at
            && $this->subscription_expires_at->endOfDay()->gte(now());
    }

    public function hasPortalAccess(): bool
    {
        return $this->hasActiveSubscription() || $this->hasActiveTrial();
    }
}
