<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlanAiService
{
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    public function generateDraft(string $prompt, array $availableFeatures): array
    {
        $apiKey = config('services.gemini.api_key');

        if (blank($apiKey)) {
            throw new \RuntimeException('Gemini API key is not configured.');
        }

        $response = null;
        $lastError = null;

        foreach ($this->candidateModels() as $model) {
            $payload = [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [['text' => $this->buildPrompt($prompt, $availableFeatures)]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.45,
                    'maxOutputTokens' => 1800,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $this->responseSchema($availableFeatures),
                ],
                'systemInstruction' => [
                    'parts' => [[
                        'text' => 'You create practical SaaS subscription plan drafts for Nigerian school management and CBT software. Return only safe JSON fields that fit the provided schema.',
                    ]],
                ],
            ];

            $response = Http::timeout(55)
                ->retry(2, 500, function ($exception) {
                    if ($exception instanceof RequestException) {
                        return in_array($exception->response->status(), [429, 500, 502, 503, 504], true);
                    }

                    return true;
                }, false)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(sprintf($this->apiUrl, $model).'?key='.$apiKey, $payload);

            if ($response->successful()) {
                break;
            }

            $lastError = $response->body();

            if (! in_array($response->status(), [404, 429, 500, 502, 503, 504], true)) {
                break;
            }
        }

        if (! $response?->successful()) {
            Log::error('Gemini plan draft error: '.$lastError);
            throw new \RuntimeException('Failed to generate plan draft using Gemini AI.');
        }

        return $this->sanitizeDraft($this->jsonFromResponse($response->json()), $availableFeatures);
    }

    private function candidateModels(): array
    {
        return array_values(array_unique(array_filter(array_merge(
            [config('services.gemini.model', 'gemini-2.5-flash')],
            config('services.gemini.fallback_models', [])
        ))));
    }

    private function buildPrompt(string $prompt, array $availableFeatures): string
    {
        $features = implode("\n- ", $availableFeatures);

        return "Create one subscription plan draft from this admin request:\n{$prompt}\n\nAvailable app features. Only choose from this exact list:\n- {$features}\n\nRules:\n- Currency is NGN.\n- monthly_price and yearly_price must be numbers.\n- yearly_price should usually be cheaper than 12 monthly payments.\n- trial_days should be between 0 and 60.\n- status must be active unless the admin asks for inactive.\n- Return only JSON.";
    }

    private function responseSchema(array $availableFeatures): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'name' => ['type' => 'STRING'],
                'slug' => ['type' => 'STRING'],
                'monthly_price' => ['type' => 'NUMBER'],
                'yearly_price' => ['type' => 'NUMBER'],
                'trial_days' => ['type' => 'INTEGER'],
                'status' => ['type' => 'STRING', 'enum' => ['active', 'inactive']],
                'is_recommended' => ['type' => 'BOOLEAN'],
                'features' => [
                    'type' => 'ARRAY',
                    'items' => ['type' => 'STRING', 'enum' => array_values($availableFeatures)],
                ],
            ],
            'required' => ['name', 'monthly_price', 'yearly_price', 'trial_days', 'status', 'features'],
        ];
    }

    private function jsonFromResponse(array $data): array
    {
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $decoded = json_decode($content, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($content, '{');
        $end = strrpos($content, '}');

        if ($start !== false && $end !== false) {
            $decoded = json_decode(substr($content, $start, $end - $start + 1), true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function sanitizeDraft(array $draft, array $availableFeatures): array
    {
        $name = Str::limit(trim((string) ($draft['name'] ?? 'AI Draft Plan')), 255, '');

        return [
            'name' => $name,
            'slug' => Str::slug($draft['slug'] ?? $name),
            'monthly_price' => max(0, (float) ($draft['monthly_price'] ?? 0)),
            'yearly_price' => max(0, (float) ($draft['yearly_price'] ?? 0)),
            'trial_days' => min(60, max(0, (int) ($draft['trial_days'] ?? 0))),
            'status' => in_array(($draft['status'] ?? 'active'), ['active', 'inactive'], true) ? $draft['status'] : 'active',
            'is_recommended' => (bool) ($draft['is_recommended'] ?? false),
            'features' => collect($draft['features'] ?? [])
                ->intersect($availableFeatures)
                ->values()
                ->all(),
        ];
    }
}
