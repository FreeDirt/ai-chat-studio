<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private string $apiKey;
    private string $model;
    private string $systemPrompt;
    private float $temperature;
    private int $maxTokens;

    public function __construct(array $config = [])
    {
        $this->apiKey      = $config['api_key']      ?? \App\Models\Setting::get('openai_api_key', '');
        $this->model       = $config['model']        ?? \App\Models\Setting::get('openai_model', 'gpt-4o-mini');
        $this->systemPrompt = $config['system_prompt'] ?? \App\Models\Setting::get('system_prompt', 'You are a helpful assistant.');
        $this->temperature = (float) ($config['temperature'] ?? \App\Models\Setting::get('temperature', 0.7));
        $this->maxTokens   = (int)   ($config['max_tokens']  ?? \App\Models\Setting::get('max_tokens', 2048));
    }

    /**
     * Send a chat message with conversation history.
     *
     * @param array $messages [['role' => 'user'|'assistant', 'content' => '...']]
     * @return array ['content' => string, 'model' => string, 'tokens_used' => int, 'response_time_ms' => int]
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
        ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
            'model'       => $this->model,
            'messages'    => $formattedMessages,
            'temperature' => $this->temperature,
            'max_tokens'  => $this->maxTokens,
        ]);

        $elapsed = (int) round((microtime(true) - $startTime) * 1000);

        if ($response->failed()) {
            Log::error('OpenAI API Error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('OpenAI API error: ' . ($response->json('error.message') ?? $response->body()));
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
     * List available OpenAI models.
     */
    public function listModels(): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->timeout(15)->get('https://api.openai.com/v1/models');

        if ($response->failed()) return [];

        return collect($response->json('data', []))
            ->filter(fn($m) => str_contains($m['id'], 'gpt'))
            ->sortBy('id')
            ->pluck('id')
            ->values()
            ->toArray();
    }
}
