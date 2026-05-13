<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Override extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'exam_id',
        'approved_by',
        'reason',
        'expiry_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isActive(): bool
    {
        return $this->is_active && Carbon::now()->lt($this->expiry_date);
    }

    public function isExpired(): bool
    {
        return Carbon::now()->gt($this->expiry_date);
    }

    public function deactivate(): void
    {
        $this->is_active = false;
        $this->save();
    }

    public function activate(): void
    {
        $this->is_active = true;
        $this->save();
    }

    public function extendExpiry(Carbon $newExpiryDate): void
    {
        $this->expiry_date = $newExpiryDate;
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('expiry_date', '>', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', Carbon::now());
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    public function scopeByApprover($query, $approverId)
    {
        return $query->where('approved_by', $approverId);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('exam_id');
    }

    public function scopeSpecific($query)
    {
        return $query->whereNotNull('exam_id');
    }
}
