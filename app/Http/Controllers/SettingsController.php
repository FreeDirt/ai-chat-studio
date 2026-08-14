<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\OllamaService;
use App\Services\OpenAIService;
use App\Services\OpenRouterService;
use App\Services\ClaudeService;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::allAsArray();

        // Try to fetch Ollama models if it's the active provider
        $ollamaModels = [];
        $ollamaOnline = false;
        if (($settings['ai_provider'] ?? '') === 'ollama') {
            try {
                $svc = new OllamaService(['url' => $settings['ollama_url'] ?? '']);
                $ollamaOnline = $svc->isReachable();
                $ollamaModels = $ollamaOnline ? $svc->listModels() : [];
            } catch (\Throwable) {}
        }

        return view('settings.index', compact('settings', 'ollamaModels', 'ollamaOnline'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'ai_provider'        => 'required|in:openai,openrouter,claude,gemini,ollama',
            'openai_api_key'     => 'nullable|string',
            'openai_model'       => 'nullable|string|max:100',
            'openrouter_api_key' => 'nullable|string',
            'openrouter_model'   => 'nullable|string|max:100',
            'claude_api_key'     => 'nullable|string',
            'claude_model'       => 'nullable|string|max:100',
            'gemini_api_key'     => 'nullable|string',
            'gemini_model'       => 'nullable|string|max:100',
            'ollama_url'         => 'nullable|string',
            'ollama_model'       => 'nullable|string|max:100',
            'system_prompt'      => 'nullable|string|max:65536',
            'temperature'        => 'nullable|numeric|min:0|max:2',
            'max_tokens'         => 'nullable|integer|min:128|max:32768',
        ]);

        $keys = [
            'ai_provider',
            'openai_api_key',     'openai_model',
            'openrouter_api_key', 'openrouter_model',
            'claude_api_key',     'claude_model',
            'gemini_api_key',     'gemini_model',
            'ollama_url',         'ollama_model',
            'system_prompt',      'temperature', 'max_tokens',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key));
            }
        }

        return response()->json(['success' => true, 'message' => 'Settings saved successfully.']);
    }

    /**
     * Test the currently active provider connection.
     */
    public function testConnection()
    {
        $provider = Setting::get('ai_provider', 'openai');

        try {
            $testMsg = [['role' => 'user', 'content' => 'Reply with exactly one word: OK']];

            $result = match ($provider) {
                'openai'     => (new OpenAIService())->chat($testMsg),
                'openrouter' => (new OpenRouterService())->chat($testMsg),
                'claude'     => (new ClaudeService())->chat($testMsg),
                'gemini'     => (new GeminiService())->chat($testMsg),
                'ollama'     => $this->testOllama($testMsg),
                default      => throw new \InvalidArgumentException("Unknown provider: $provider"),
            };

            return response()->json([
                'success'  => true,
                'response' => trim($result['content']),
                'model'    => $result['model'],
                'time_ms'  => $result['response_time_ms'],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Fetch available models for any provider.
     */
    public function fetchModels(Request $request)
    {
        $provider = $request->query('provider', Setting::get('ai_provider', 'openai'));

        try {
            $models = match ($provider) {
                'openai'     => (new OpenAIService())->listModels(),
                'openrouter' => (new OpenRouterService())->listModels(),
                'claude'     => (new ClaudeService())->listModels(),
                'gemini'     => (new GeminiService())->listModels(),
                default      => (new OllamaService())->listModels(),
            };

            return response()->json(['models' => $models]);
        } catch (\Throwable $e) {
            return response()->json(['models' => [], 'error' => $e->getMessage()]);
        }
    }

    private function testOllama(array $messages): array
    {
        $svc = new OllamaService();
        if (!$svc->isReachable()) {
            throw new \RuntimeException('Ollama server is not reachable at the configured URL.');
        }
        return $svc->chat($messages);
    }
}
