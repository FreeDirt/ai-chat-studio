<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterService
{
    private string $apiKey;
    private string $model;
    private string $systemPrompt;
    private float  $temperature;
    private int    $maxTokens;

    public function __construct(array $config = [])
    {
        $this->apiKey       = $config['api_key']      ?? \App\Models\Setting::get('openrouter_api_key', '');
        $this->model        = $config['model']        ?? \App\Models\Setting::get('openrouter_model', 'openai/gpt-4o-mini');
        $this->systemPrompt = $config['system_prompt'] ?? \App\Models\Setting::get('system_prompt', 'You are a helpful assistant.');
        $this->temperature  = (float) ($config['temperature'] ?? \App\Models\Setting::get('temperature', 0.7));
        $this->maxTokens    = (int)   ($config['max_tokens']  ?? \App\Models\Setting::get('max_tokens', 2048));
    }

    /**
     * Send a chat message — OpenRouter uses an OpenAI-compatible format.
     */
    public function chat(array $messages): array
    {
        $startTime = microtime(true);

        $formattedMessages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
            ...$messages,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => config('app.url', 'http://localhost:8015'),
            'X-Title'       => 'AI Chat UI',
        ])->timeout(120)->post('https://openrouter.ai/api/v1/chat/completions', [
            'model'       => $this->model,
            'messages'    => $formattedMessages,
            'temperature' => $this->temperature,
            'max_tokens'  => $this->maxTokens,
        ]);

        $elapsed = (int) round((microtime(true) - $startTime) * 1000);

        if ($response->failed()) {
            Log::error('OpenRouter API Error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('OpenRouter error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $data = $response->json();

        return [
            'content'          => $data['choices'][0]['message']['content'] ?? '',
            'model'            => $data['model'] ?? $this->model,
            'tokens_used'      => $data['usage']['total_tokens'] ?? null,
            'response_time_ms' => $elapsed,
        ];
    }

    /**
     * Fetch available models from OpenRouter.
     */
    public function listModels(): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->timeout(15)->get('https://openrouter.ai/api/v1/models');

        if ($response->failed()) return [];

        return collect($response->json('data', []))
            ->sortBy('id')
            ->pluck('id')
            ->values()
            ->toArray();
    }
}
