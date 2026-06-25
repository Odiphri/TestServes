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

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'time_expired_at' => 'datetime',
        'score' => 'integer',
        'total_points' => 'integer',
        'percentage' => 'decimal:2',
        'is_submitted' => 'boolean',
        'answers' => 'array',
    ];

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
        return $this->getTimeRemainingAttribute() === 0;
    }

    public static function normalizeAnswers(array|string|null $answers): array
    {
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?: [];
        }

        if (! is_array($answers)) {
            return [];
        }

        $normalized = [];

        foreach ($answers as $questionId => $answer) {
            if ($answer === null || $answer === '') {
                continue;
            }

            $questionId = (string) $questionId;
            $answer = strtoupper(trim((string) $answer));

            if ($questionId !== '' && in_array($answer, ['A', 'B', 'C', 'D'], true)) {
                $normalized[$questionId] = $answer;
            }
        }

        return $normalized;
    }

    public function calculateScore(array|string|null $answers = null): void
    {
        $score = 0;
        $answers = self::normalizeAnswers($answers ?? $this->answers);
        $questions = $this->relationLoaded('exam')
            ? $this->exam->questions
            : $this->exam()->firstOrFail()->questions()->get();

        foreach ($questions as $question) {
            $questionId = $question->id;
            $given = $answers[$questionId] ?? $answers[(string) $questionId] ?? null;

            if ($question->isCorrectAnswer($given)) {
                $score += $question->points;
            }
        }

        $this->score = $score;
        $this->total_points = $questions->sum('points');
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
        $this->answers = self::normalizeAnswers($this->answers);
        $this->calculateScore($this->answers);
        $this->save();
    }

    public function autoSubmit(): void
    {
        $this->time_expired_at = Carbon::now();
        $this->submit();
    }

    public function getAnswerForQuestion(int $questionId): ?string
    {
        $answers = self::normalizeAnswers($this->answers);
        return $answers[$questionId] ?? $answers[(string) $questionId] ?? null;
    }

    public function setAnswerForQuestion(int $questionId, string $answer): void
    {
        $answers = self::normalizeAnswers($this->answers);
        $answers[(string) $questionId] = strtoupper(trim($answer));
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
