<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'request_type',
        'old_value',
        'new_value',
        'reason',
        'status',
        'approved_by',
        'approval_notes',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve(User $approver, ?string $notes = null): void
    {
        $this->status = 'approved';
        $this->approved_by = $approver->id;
        $this->approval_notes = $notes;
        $this->approved_at = now();
        $this->save();

        $this->applyChange();
    }

    public function reject(User $approver, ?string $notes = null): void
    {
        $this->status = 'rejected';
        $this->approved_by = $approver->id;
        $this->approval_notes = $notes;
        $this->save();
    }

    private function applyChange(): void
    {
        $student = $this->student;

        switch ($this->request_type) {
            case 'name_change':
                $names = explode(' ', $this->new_value, 2);
                $student->first_name = $names[0] ?? '';
                $student->last_name = $names[1] ?? '';
                break;
            case 'role_change':
                $student->role = $this->new_value;
                break;
            case 'prefect_title':
                $student->prefect_title = $this->new_value;
                break;
        }

        $student->save();
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('request_type', $type);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
