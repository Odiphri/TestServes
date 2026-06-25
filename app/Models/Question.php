<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
        'image_path',
        'points',
        'is_ai_generated',
        'ai_generation_prompt',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_ai_generated' => 'boolean',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function getOptionsAttribute(): array
    {
        return [
            'A' => $this->option_a,
            'B' => $this->option_b,
            'C' => $this->option_c,
            'D' => $this->option_d,
        ];
    }

    public function getCorrectOptionAttribute(): string
    {
        return $this->options[$this->correct_answer] ?? '';
    }

    public function isCorrectAnswer(?string $answer): bool
    {
        if ($answer === null) {
            return false;
        }

        return strtoupper(trim($answer)) === strtoupper(trim($this->correct_answer));
    }

    public function scopeByExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    public function scopeAIGenerated($query)
    {
        return $query->where('is_ai_generated', true);
    }

    public function scopeManual($query)
    {
        return $query->where('is_ai_generated', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
