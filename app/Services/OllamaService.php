<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    private string $baseUrl;
    private string $model;
    private string $systemPrompt;
    private float $temperature;

    public function __construct(array $config = [])
    {
        $this->baseUrl      = rtrim($config['url'] ?? \App\Models\Setting::get('ollama_url', 'http://host.docker.internal:11434'), '/');
        $this->model        = $config['model']         ?? \App\Models\Setting::get('ollama_model', 'llama3');
        $this->systemPrompt = $config['system_prompt'] ?? \App\Models\Setting::get('system_prompt', 'You are a helpful assistant.');
        $this->temperature  = (float) ($config['temperature'] ?? \App\Models\Setting::get('temperature', 0.7));
    }

    /**
     * Send a chat message using Ollama's chat API (with history).
     *
     * @param array $messages [['role' => 'user'|'assistant', 'content' => '...']]
     * @return array ['content' => string, 'model' => string, 'tokens_used' => int|null, 'response_time_ms' => int]
     */
    public function chat(array $messages): array
    {
        $startTime = microtime(true);

        $formattedMessages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
            ...$messages,
        ];

        $response = Http::timeout(300)->post($this->baseUrl . '/api/chat', [
            'model'    => $this->model,
            'messages' => $formattedMessages,
            'stream'   => false,
            'options'  => [
                'temperature' => $this->temperature,
            ],
        ]);

        $elapsed = (int) round((microtime(true) - $startTime) * 1000);

        if ($response->failed()) {
            Log::error('Ollama API Error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Ollama API error: ' . $response->body());
        }

        $data = $response->json();

        return [
            'content'          => $data['message']['content'] ?? '',
            'model'            => $data['model'] ?? $this->model,
            'tokens_used'      => isset($data['eval_count']) ? ($data['prompt_eval_count'] ?? 0) + $data['eval_count'] : null,
            'response_time_ms' => $elapsed,
        ];
    }

    /**
     * List locally available Ollama models.
     */
    public function listModels(): array
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/api/tags');
            if ($response->failed()) return [];
            return collect($response->json('models', []))->pluck('name')->toArray();
        } catch (\Throwable $e) {
            Log::warning('Could not reach Ollama: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if Ollama server is reachable.
     */
    public function isReachable(): bool
    {
        try {
            return Http::timeout(5)->get($this->baseUrl)->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
