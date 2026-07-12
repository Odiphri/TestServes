<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\PublicDiskUrl;

class PaymentRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'school_owner_id',
        'subscription_plan_id',
        'amount',
        'currency',
        'payment_method',
        'payment_reference',
        'status',
        'payment_date',
        'period_start',
        'period_end',
        'receipt_number',
        'evidence_path',
        'notes',
        'approved_by_admin_id',
        'approved_at',
        'rejected_by_admin_id',
        'rejected_at',
        'verified_at',
        'provider_reference',
        'provider_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'period_start' => 'date',
            'period_end' => 'date',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'verified_at' => 'datetime',
            'provider_payload' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(SchoolOwner::class, 'school_owner_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'approved_by_admin_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'rejected_by_admin_id');
    }

    public function getEvidenceUrlAttribute(): ?string
    {
        return PublicDiskUrl::make($this->evidence_path);
    }
}
