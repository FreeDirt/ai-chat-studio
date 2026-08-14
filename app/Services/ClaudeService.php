<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    private string $apiKey;
    private string $model;
    private string $systemPrompt;
    private float  $temperature;
    private int    $maxTokens;

    // Official Anthropic models
    public const MODELS = [
        'claude-opus-4-5',
        'claude-sonnet-4-5',
        'claude-haiku-4-5',
        'claude-3-5-sonnet-20241022',
        'claude-3-5-haiku-20241022',
        'claude-3-opus-20240229',
        'claude-3-sonnet-20240229',
        'claude-3-haiku-20240307',
    ];

    public function __construct(array $config = [])
    {
        $this->apiKey       = $config['api_key']      ?? \App\Models\Setting::get('claude_api_key', '');
        $this->model        = $config['model']        ?? \App\Models\Setting::get('claude_model', 'claude-3-5-sonnet-20241022');
        $this->systemPrompt = $config['system_prompt'] ?? \App\Models\Setting::get('system_prompt', 'You are a helpful assistant.');
        $this->temperature  = (float) ($config['temperature'] ?? \App\Models\Setting::get('temperature', 0.7));
        $this->maxTokens    = (int)   ($config['max_tokens']  ?? \App\Models\Setting::get('max_tokens', 2048));
    }

    /**
     * Send a chat message using the Anthropic Messages API.
     * Claude uses a different format: system prompt is top-level,
     * and roles must strictly alternate user/assistant.
     */
    public function chat(array $messages): array
    {
        $startTime = microtime(true);

        // Claude requires strictly alternating user/assistant roles.
        // Filter out any system messages and normalise.
        $filtered = array_values(array_filter($messages, fn($m) => in_array($m['role'], ['user', 'assistant'])));

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type'      => 'application/json',
        ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
            'model'      => $this->model,
            'max_tokens' => $this->maxTokens,
            'system'     => $this->systemPrompt,
            'messages'   => $filtered,
        ]);

        $elapsed = (int) round((microtime(true) - $startTime) * 1000);

        if ($response->failed()) {
            Log::error('Claude API Error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Claude error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $data        = $response->json();
        $inputTokens = $data['usage']['input_tokens']  ?? 0;
        $outTokens   = $data['usage']['output_tokens'] ?? 0;

        return [
            'content'          => $data['content'][0]['text'] ?? '',
            'model'            => $data['model'] ?? $this->model,
            'tokens_used'      => $inputTokens + $outTokens ?: null,
            'response_time_ms' => $elapsed,
        ];
    }

    /**
     * Return the static list of known Claude models.
     */
    public function listModels(): array
    {
        return self::MODELS;
    }
}
