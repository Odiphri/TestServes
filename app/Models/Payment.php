<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_class_id',
        'total_fees',
        'amount_paid',
        'status',
        'payment_details',
        'last_payment_date',
    ];

    protected function casts(): array
    {
        return [
            'total_fees' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance' => 'decimal:2',
            'last_payment_date' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function hasBalance(): bool
    {
        return $this->balance > 0;
    }

    public function addPayment(float $amount): void
    {
        $this->amount_paid += $amount;
        $this->last_payment_date = now();

        $balance = $this->total_fees - $this->amount_paid;

        if ($balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partial';
        }

        $this->save();
    }

    public function getPaidPercentageAttribute(): float
    {
        return $this->total_fees > 0 ? round(($this->amount_paid / $this->total_fees) * 100, 1) : 100.0;
    }

    public function getUnpaidPercentageAttribute(): float
    {
        return max(0, round(100 - $this->paid_percentage, 1));
    }

    public function canAccessExams(): bool
    {
        return $this->isPaid() || $this->student->overrides()->active()->exists();
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('school_class_id', $classId);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeWithBalance($query)
    {
        return $query->where('balance', '>', 0);
    }
}
