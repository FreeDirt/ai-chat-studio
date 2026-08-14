<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $systemPrompt;
    private float  $temperature;
    private int    $maxTokens;

    // Well-known Gemini models
    public const MODELS = [
        'gemini-2.5-pro',
        'gemini-2.5-flash',
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
        'gemini-1.5-pro',
        'gemini-1.5-flash',
        'gemini-1.5-flash-8b',
    ];

    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct(array $config = [])
    {
        $this->apiKey       = $config['api_key']      ?? \App\Models\Setting::get('gemini_api_key', '');
        $this->model        = $config['model']        ?? \App\Models\Setting::get('gemini_model', 'gemini-2.0-flash');
        $this->systemPrompt = $config['system_prompt'] ?? \App\Models\Setting::get('system_prompt', 'You are a helpful assistant.');
        $this->temperature  = (float) ($config['temperature'] ?? \App\Models\Setting::get('temperature', 0.7));
        $this->maxTokens    = (int)   ($config['max_tokens']  ?? \App\Models\Setting::get('max_tokens', 2048));
    }

    /**
     * Send a chat message using the native Gemini API.
     * Gemini uses "user"/"model" roles and a different message structure.
     */
    public function chat(array $messages): array
    {
        $startTime = microtime(true);

        // Map "assistant" → "model" (Gemini's terminology)
        $contents = array_map(fn($m) => [
            'role'  => $m['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => $m['content']]],
        ], array_filter($messages, fn($m) => in_array($m['role'], ['user', 'assistant'])));

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $this->systemPrompt]],
            ],
            'contents'           => array_values($contents),
            'generationConfig'   => [
                'temperature'     => $this->temperature,
                'maxOutputTokens' => $this->maxTokens,
            ],
        ];

        $url = self::BASE_URL . '/models/' . $this->model . ':generateContent?key=' . $this->apiKey;

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(120)
            ->post($url, $payload);

        $elapsed = (int) round((microtime(true) - $startTime) * 1000);

        if ($response->failed()) {
            Log::error('Gemini API Error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Gemini error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $data = $response->json();

        return [
            'content'          => $data['candidates'][0]['content']['parts'][0]['text'] ?? '',
            'model'            => $this->model,
            'tokens_used'      => $data['usageMetadata']['totalTokenCount'] ?? null,
            'response_time_ms' => $elapsed,
        ];
    }

    /**
     * Fetch available Gemini models from the API.
     */
    public function listModels(): array
    {
        try {
            $url      = self::BASE_URL . '/models?key=' . $this->apiKey;
            $response = Http::timeout(10)->get($url);

            if ($response->failed()) return self::MODELS;

            return collect($response->json('models', []))
                ->filter(fn($m) => str_contains($m['name'] ?? '', 'gemini'))
                ->map(fn($m) => str_replace('models/', '', $m['name']))
                ->sort()
                ->values()
                ->toArray() ?: self::MODELS;
        } catch (\Throwable $e) {
            Log::warning('Gemini model list fallback: ' . $e->getMessage());
            return self::MODELS;
        }
    }
}
