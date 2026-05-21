<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'subject_id',
        'school_class_id',
        'target_class_ids',
        'created_by',
        'duration_minutes',
        'start_time',
        'end_time',
        'shuffle_questions',
        'show_results',
        'is_live',
        'allow_review',
        'pass_mark',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'target_class_ids' => 'array',
            'shuffle_questions' => 'boolean',
            'show_results' => 'boolean',
            'is_live' => 'boolean',
            'allow_review' => 'boolean',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->questions->sum('points');
    }

    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->is_live && $now->between($this->start_time, $this->end_time);
    }

    public function isUpcoming(): bool
    {
        return Carbon::now()->lt($this->start_time);
    }

    public function hasEnded(): bool
    {
        return Carbon::now()->gt($this->end_time);
    }

    public function canBeTakenByUser(User $user): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if (!$user->isStudent()) {
            return false;
        }

        $hasAttempt = $this->attempts()
            ->where('student_id', $user->id)
            ->where('is_submitted', true)
            ->exists();

        return !$hasAttempt;
    }

    public function targetsClass(int $classId): bool
    {
        $targetClassIds = collect($this->target_class_ids ?: [$this->school_class_id])
            ->filter()
            ->map(fn ($targetClassId) => (int) $targetClassId);

        return $targetClassIds->contains($classId);
    }

    public function targetClasses()
    {
        return SchoolClass::whereIn('id', $this->target_class_ids ?: [$this->school_class_id]);
    }

    public function getTargetClassNamesAttribute(): string
    {
        $targetClassIds = $this->target_class_ids ?: [$this->school_class_id];

        return SchoolClass::whereIn('id', $targetClassIds)
            ->orderBy('level')
            ->orderBy('stream')
            ->get()
            ->pluck('full_name')
            ->join(', ');
    }

    public function getStudentAttempt(User $user): ?ExamAttempt
    {
        return $this->attempts()
            ->where('student_id', $user->id)
            ->first();
    }

    public function scopeLive($query)
    {
        return $query->where('is_live', true);
    }

    public function scopeActive($query)
    {
        $now = Carbon::now();
        return $query->where('is_live', true)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('school_class_id', $classId);
    }

    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByCreator($query, $creatorId)
    {
        return $query->where('created_by', $creatorId);
    }
}
