<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class AIService
{
    protected $apiKey;
    protected $apiUrl;
    protected $model;
    protected $fallbackModels;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-2.5-flash');
        $this->fallbackModels = config('services.gemini.fallback_models', []);
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';
    }

    public function generateQuestions($topic, $numberOfQuestions = 5, $difficulty = 'medium', int $pointsPerQuestion = 1, ?int $overallPoints = null)
    {
        if (blank($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured.');
        }

        $prompt = $this->buildPrompt($topic, $numberOfQuestions, $difficulty, $pointsPerQuestion, $overallPoints);

        $response = null;
        $lastError = null;

        foreach ($this->candidateModels() as $model) {
            $response = Http::timeout(55)
                ->retry(2, 500, function ($exception) {
                    if ($exception instanceof RequestException) {
                        return in_array($exception->response->status(), [429, 500, 502, 503, 504], true);
                    }

                    return true;
                }, false)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(sprintf($this->apiUrl, $model) . '?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => $this->maxOutputTokens((int) $numberOfQuestions),
                        'responseMimeType' => 'application/json',
                        'responseSchema' => $this->questionResponseSchema(),
                    ],
                    'systemInstruction' => [
                        'parts' => [
                            [
                                'text' => 'You are an educational content creator specializing in multiple-choice questions for high school students. Always create questions with 4 options (A, B, C, D) where only one option is correct.',
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                break;
            }

            $lastError = $response->body();

            if (! in_array($response->status(), [404, 429, 500, 502, 503, 504], true)) {
                break;
            }
        }

        if (!$response?->successful()) {
            Log::error('Gemini API Error: ' . $lastError);
            throw new \Exception('Failed to generate questions using Gemini AI');
        }

        $data = $response->json();
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return $this->parseQuestions($content, $pointsPerQuestion);
    }

    private function candidateModels(): array
    {
        return array_values(array_unique(array_filter(array_merge([$this->model], $this->fallbackModels))));
    }

    private function maxOutputTokens(int $numberOfQuestions): int
    {
        return min(8192, max(2500, 900 + ($numberOfQuestions * 450)));
    }

    private function questionResponseSchema(): array
    {
        return [
            'type' => 'ARRAY',
            'items' => [
                'type' => 'OBJECT',
                'properties' => [
                    'question' => ['type' => 'STRING'],
                    'option_a' => ['type' => 'STRING'],
                    'option_b' => ['type' => 'STRING'],
                    'option_c' => ['type' => 'STRING'],
                    'option_d' => ['type' => 'STRING'],
                    'correct_answer' => [
                        'type' => 'STRING',
                        'enum' => ['A', 'B', 'C', 'D'],
                    ],
                ],
                'required' => ['question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer'],
            ],
        ];
    }

    private function buildPrompt($topic, $numberOfQuestions, $difficulty, int $pointsPerQuestion, ?int $overallPoints)
    {
        $overallPointInstruction = $overallPoints
            ? "The generated set is planned to be scored over {$overallPoints} total point(s). Each question must carry {$pointsPerQuestion} point(s), so the generated questions should total " . ($numberOfQuestions * $pointsPerQuestion) . " point(s)."
            : "Each question must carry {$pointsPerQuestion} point(s).";

        return "Generate {$numberOfQuestions} multiple-choice questions about '{$topic}' for high school students.

Requirements:
1. Difficulty level: {$difficulty}
2. Each question must have exactly 4 options (A, B, C, D)
3. Only one option should be correct
4. Include the correct answer for each question
5. {$overallPointInstruction}
6. Return only valid JSON. Do not include markdown, prose, or code fences.
7. Format as JSON array with this structure:
[
  {
    \"question\": \"Question text here\",
    \"option_a\": \"Option A\",
    \"option_b\": \"Option B\",
    \"option_c\": \"Option C\",
    \"option_d\": \"Option D\",
    \"correct_answer\": \"A\"
  }
]

Please generate exactly {$numberOfQuestions} questions following this format.";
    }

    private function parseQuestions($content, int $pointsPerQuestion = 1)
    {
        try {
            $questions = json_decode($content, true);

            if (! is_array($questions)) {
                // Extract JSON from responses that still include surrounding text or fences.
                $jsonStart = strpos($content, '[');
                $jsonEnd = strrpos($content, ']');

                if ($jsonStart !== false && $jsonEnd !== false) {
                    $jsonString = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                    $questions = json_decode($jsonString, true);
                }
            }

            if (is_array($questions)) {
                return array_values(array_filter(array_map(function ($question) use ($pointsPerQuestion) {
                    $correctAnswer = strtoupper($question['correct_answer'] ?? 'A');

                    if (! in_array($correctAnswer, ['A', 'B', 'C', 'D'], true)) {
                        $correctAnswer = 'A';
                    }

                    return [
                        'question_text' => $question['question'] ?? $question['question_text'] ?? '',
                        'option_a' => $question['option_a'] ?? '',
                        'option_b' => $question['option_b'] ?? '',
                        'option_c' => $question['option_c'] ?? '',
                        'option_d' => $question['option_d'] ?? '',
                        'correct_answer' => $correctAnswer,
                        'points' => $pointsPerQuestion,
                    ];
                }, $questions), fn ($question) => filled($question['question_text'])));
            }
        } catch (\Exception $e) {
            Log::error('Error parsing Gemini response: ' . $e->getMessage());
        }

        return [];
    }
}
