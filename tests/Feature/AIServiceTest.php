<?php

namespace Tests\Feature;

use App\Services\AIService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AIServiceTest extends TestCase
{
    public function test_ai_service_generates_questions_with_gemini(): void
    {
        Config::set('services.gemini.api_key', 'test-gemini-key');
        Config::set('services.gemini.model', 'gemini-2.5-flash');
        Config::set('services.gemini.fallback_models', ['gemini-2.0-flash']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        [
                                            'question' => 'What is 2 + 2?',
                                            'option_a' => '3',
                                            'option_b' => '4',
                                            'option_c' => '5',
                                            'option_d' => '6',
                                            'correct_answer' => 'B',
                                        ],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $questions = app(AIService::class)->generateQuestions('Addition', 1, 'easy');

        $this->assertSame('What is 2 + 2?', $questions[0]['question_text']);
        $this->assertSame('4', $questions[0]['option_b']);
        $this->assertSame('B', $questions[0]['correct_answer']);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return str_contains($request->url(), 'gemini-2.5-flash:generateContent')
                && str_contains($request->url(), 'key=test-gemini-key')
                && ($payload['generationConfig']['responseMimeType'] ?? null) === 'application/json';
        });
    }

    public function test_ai_service_falls_back_when_configured_gemini_model_is_unavailable(): void
    {
        Config::set('services.gemini.api_key', 'test-gemini-key');
        Config::set('services.gemini.model', 'gemini-1.5-flash');
        Config::set('services.gemini.fallback_models', ['gemini-2.5-flash']);

        Http::fake([
            '*gemini-1.5-flash*' => Http::response(['error' => ['status' => 'NOT_FOUND']], 404),
            '*gemini-2.5-flash*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        [
                                            'question' => 'Fallback question?',
                                            'option_a' => 'A',
                                            'option_b' => 'B',
                                            'option_c' => 'C',
                                            'option_d' => 'D',
                                            'correct_answer' => 'A',
                                        ],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $questions = app(AIService::class)->generateQuestions('Fallback', 1, 'easy');

        $this->assertSame('Fallback question?', $questions[0]['question_text']);
        Http::assertSentCount(2);
    }
}
