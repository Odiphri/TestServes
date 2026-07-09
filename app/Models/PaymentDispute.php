<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentDispute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_record_id',
        'school_id',
        'school_owner_id',
        'assigned_admin_id',
        'reference',
        'subject',
        'description',
        'disputed_amount',
        'status',
        'priority',
        'finance_notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'disputed_amount' => 'decimal:2',
            'resolved_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PaymentRecord::class, 'payment_record_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(SchoolOwner::class, 'school_owner_id');
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'assigned_admin_id');
    }
}
