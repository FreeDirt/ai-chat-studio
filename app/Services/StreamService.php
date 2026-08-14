<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class StreamService
{
    /**
     * Stream a response from the active AI provider.
     * Invokes callback $onChunk(string $chunk) for each text fragment received.
     * Returns full array ['content' => string, 'model' => string, 'response_time_ms' => int].
     */
    public function stream(array $messages, ?string $systemPromptOverride = null, ?callable $onChunk = null): array
    {
        $provider = Setting::get('ai_provider', 'openai');

        return match ($provider) {
            'openai'     => $this->streamOpenAI($messages, $systemPromptOverride, $onChunk),
            'openrouter' => $this->streamOpenRouter($messages, $systemPromptOverride, $onChunk),
            'claude'     => $this->streamClaude($messages, $systemPromptOverride, $onChunk),
            'gemini'     => $this->streamGemini($messages, $systemPromptOverride, $onChunk),
            'ollama'     => $this->streamOllama($messages, $systemPromptOverride, $onChunk),
            default      => throw new \InvalidArgumentException("Unknown provider: $provider"),
        };
    }

    // ===== OPENAI =====
    private function streamOpenAI(array $messages, ?string $systemPrompt, ?callable $onChunk): array
    {
        $apiKey       = Setting::get('openai_api_key', '');
        $model        = Setting::get('openai_model', 'gpt-4o-mini');
        $prompt       = $systemPrompt ?? Setting::get('system_prompt', 'You are a helpful assistant.');
        $temperature  = (float) Setting::get('temperature', 0.7);
        $maxTokens    = (int) Setting::get('max_tokens', 2048);

        $payload = [
            'model'       => $model,
            'messages'    => [['role' => 'system', 'content' => $prompt], ...$messages],
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens,
            'stream'      => true,
        ];

        return $this->curlSsePost(
            'https://api.openai.com/v1/chat/completions',
            ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json'],
            json_encode($payload),
            $model,
            function ($dataLine) use ($onChunk) {
                if ($dataLine === '[DONE]') return null;
                $json = json_decode($dataLine, true);
                $text = $json['choices'][0]['delta']['content'] ?? '';
                if ($text !== '' && $onChunk) $onChunk($text);
                return $text;
            }
        );
    }

    // ===== OPENROUTER =====
    private function streamOpenRouter(array $messages, ?string $systemPrompt, ?callable $onChunk): array
    {
        $apiKey       = Setting::get('openrouter_api_key', '');
        $model        = Setting::get('openrouter_model', 'openai/gpt-4o-mini');
        $prompt       = $systemPrompt ?? Setting::get('system_prompt', 'You are a helpful assistant.');
        $temperature  = (float) Setting::get('temperature', 0.7);
        $maxTokens    = (int) Setting::get('max_tokens', 2048);

        $payload = [
            'model'       => $model,
            'messages'    => [['role' => 'system', 'content' => $prompt], ...$messages],
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens,
            'stream'      => true,
        ];

        return $this->curlSsePost(
            'https://openrouter.ai/api/v1/chat/completions',
            [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . config('app.url', 'http://localhost:8015'),
                'X-Title: AI Chat UI',
            ],
            json_encode($payload),
            $model,
            function ($dataLine) use ($onChunk) {
                if ($dataLine === '[DONE]') return null;
                $json = json_decode($dataLine, true);
                $text = $json['choices'][0]['delta']['content'] ?? '';
                if ($text !== '' && $onChunk) $onChunk($text);
                return $text;
            }
        );
    }

    // ===== CLAUDE (ANTHROPIC) =====
    private function streamClaude(array $messages, ?string $systemPrompt, ?callable $onChunk): array
    {
        $apiKey       = Setting::get('claude_api_key', '');
        $model        = Setting::get('claude_model', 'claude-3-5-sonnet-20241022');
        $prompt       = $systemPrompt ?? Setting::get('system_prompt', 'You are a helpful assistant.');
        $maxTokens    = (int) Setting::get('max_tokens', 2048);

        $filtered = array_values(array_filter($messages, fn($m) => in_array($m['role'], ['user', 'assistant'])));

        $payload = [
            'model'      => $model,
            'max_tokens' => $maxTokens,
            'system'     => $prompt,
            'messages'   => $filtered,
            'stream'     => true,
        ];

        return $this->curlSsePost(
            'https://api.anthropic.com/v1/messages',
            [
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
                'Content-Type: application/json',
            ],
            json_encode($payload),
            $model,
            function ($dataLine) use ($onChunk) {
                $json = json_decode($dataLine, true);
                if (($json['type'] ?? '') === 'content_block_delta') {
                    $text = $json['delta']['text'] ?? '';
                    if ($text !== '' && $onChunk) $onChunk($text);
                    return $text;
                }
                return null;
            }
        );
    }

    // ===== GEMINI (GOOGLE) =====
    private function streamGemini(array $messages, ?string $systemPrompt, ?callable $onChunk): array
    {
        $apiKey       = Setting::get('gemini_api_key', '');
        $model        = Setting::get('gemini_model', 'gemini-2.0-flash');
        $prompt       = $systemPrompt ?? Setting::get('system_prompt', 'You are a helpful assistant.');
        $temperature  = (float) Setting::get('temperature', 0.7);
        $maxTokens    = (int) Setting::get('max_tokens', 2048);

        $contents = array_map(fn($m) => [
            'role'  => $m['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => $m['content']]],
        ], array_filter($messages, fn($m) => in_array($m['role'], ['user', 'assistant'])));

        $payload = [
            'system_instruction' => ['parts' => [['text' => $prompt]]],
            'contents'           => array_values($contents),
            'generationConfig'   => ['temperature' => $temperature, 'maxOutputTokens' => $maxTokens],
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:streamGenerateContent?alt=sse&key={$apiKey}";

        return $this->curlSsePost(
            $url,
            ['Content-Type: application/json'],
            json_encode($payload),
            $model,
            function ($dataLine) use ($onChunk) {
                $json = json_decode($dataLine, true);
                $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if ($text !== '' && $onChunk) $onChunk($text);
                return $text;
            }
        );
    }

    // ===== OLLAMA =====
    private function streamOllama(array $messages, ?string $systemPrompt, ?callable $onChunk): array
    {
        $baseUrl = rtrim(Setting::get('ollama_url', 'http://host.docker.internal:11434'), '/');
        $model   = Setting::get('ollama_model', 'llama3');
        $prompt  = $systemPrompt ?? Setting::get('system_prompt', 'You are a helpful assistant.');

        $formattedMessages = [['role' => 'system', 'content' => $prompt], ...$messages];

        $payload = [
            'model'    => $model,
            'messages' => $formattedMessages,
            'stream'   => true,
        ];

        return $this->curlSsePost(
            $baseUrl . '/api/chat',
            ['Content-Type: application/json'],
            json_encode($payload),
            $model,
            function ($dataLine) use ($onChunk) {
                $json = json_decode($dataLine, true);
                $text = $json['message']['content'] ?? '';
                if ($text !== '' && $onChunk) $onChunk($text);
                return $text;
            },
            true // Ollama uses newline-delimited JSON
        );
    }

    // ===== GENERIC cURL SSE HARVESTER =====
    private function curlSsePost(string $url, array $headers, string $jsonPayload, string $modelName, callable $parseCallback, bool $isNdj = false): array
    {
        $startTime   = microtime(true);
        $fullContent = '';
        $buffer      = '';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use (&$buffer, &$fullContent, $parseCallback, $isNdj) {
            $buffer .= $chunk;

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line   = trim(substr($buffer, 0, $pos));
                $buffer = substr($buffer, $pos + 1);

                if (empty($line)) continue;

                if ($isNdj) {
                    $text = $parseCallback($line);
                    if ($text) $fullContent .= $text;
                } else {
                    if (str_starts_with($line, 'data: ')) {
                        $dataLine = trim(substr($line, 6));
                        $text = $parseCallback($dataLine);
                        if ($text) $fullContent .= $text;
                    }
                }
            }

            return strlen($chunk);
        });

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errNo    = curl_errno($ch);
        $errMsg   = curl_error($ch);
        curl_close($ch);

        $elapsed = (int) round((microtime(true) - $startTime) * 1000);

        if ($errNo || $httpCode >= 400) {
            Log::error("Stream error ({$httpCode}): {$errMsg}");
            throw new \RuntimeException("AI Stream error (HTTP {$httpCode}): " . ($fullContent ?: $errMsg));
        }

        return [
            'content'          => $fullContent,
            'model'            => $modelName,
            'tokens_used'      => (int) (mb_strlen($fullContent) / 4), // estimate
            'response_time_ms' => $elapsed,
        ];
    }
}
