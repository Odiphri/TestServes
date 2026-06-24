<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'started_at',
        'submitted_at',
        'time_expired_at',
        'score',
        'total_points',
        'percentage',
        'grade',
        'is_submitted',
        'answers',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'time_expired_at' => 'datetime',
            'score' => 'integer',
            'total_points' => 'integer',
            'percentage' => 'decimal:2',
            'is_submitted' => 'boolean',
            'answers' => 'array',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function getTimeRemainingAttribute(): int
    {
        if ($this->is_submitted) {
            return 0;
        }

        $now = Carbon::now();
        $endTime = $this->time_expired_at ?: $this->started_at->addMinutes($this->exam->duration_minutes);
        
        return max(0, $now->diffInSeconds($endTime, false));
    }

    public function isExpired(): bool
    {
        return $this->getTimeRemainingAttribute === 0;
    }

    public function calculateScore(): void
    {
        $score = 0;
        $answers = $this->answers ?? [];
        
        foreach ($this->exam->questions as $question) {
            $questionId = $question->id;
            if (isset($answers[$questionId]) && $question->isCorrectAnswer($answers[$questionId])) {
                $score += $question->points;
            }
        }

        $this->score = $score;
        $this->total_points = $this->exam->total_points;
        $this->percentage = $this->total_points > 0 ? ($score / $this->total_points) * 100 : 0;
        $this->grade = $this->calculateGrade();
    }

    private function calculateGrade(): string
    {
        $percentage = $this->percentage;

        if ($percentage >= 90) return 'A+';
        if ($percentage >= 85) return 'A';
        if ($percentage >= 80) return 'A-';
        if ($percentage >= 75) return 'B+';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 65) return 'B-';
        if ($percentage >= 60) return 'C+';
        if ($percentage >= 55) return 'C';
        if ($percentage >= 50) return 'C-';
        if ($percentage >= 45) return 'D';
        if ($percentage >= 40) return 'E';
        return 'F';
    }

    public function submit(): void
    {
        $this->is_submitted = true;
        $this->submitted_at = Carbon::now();
        $this->calculateScore();
        $this->save();
    }

    public function autoSubmit(): void
    {
        $this->time_expired_at = Carbon::now();
        $this->submit();
    }

    public function getAnswerForQuestion(int $questionId): ?string
    {
        $answers = $this->answers ?? [];
        return $answers[$questionId] ?? null;
    }

    public function setAnswerForQuestion(int $questionId, string $answer): void
    {
        $answers = $this->answers ?? [];
        $answers[$questionId] = $answer;
        $this->answers = $answers;
        $this->save();
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('is_submitted', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_submitted', false);
    }
}
