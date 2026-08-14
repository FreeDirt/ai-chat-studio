<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class EmbeddingService
{
    private string $provider;
    private string $model;
    private const LOCAL_VECTOR_DIM = 256;

    public function __construct()
    {
        $chatProvider = Setting::get('ai_provider', 'openai');

        // Auto-detect embedding provider from active chat provider & available keys
        $this->provider = $this->resolveProvider($chatProvider);
        $this->model    = $this->resolveModel($this->provider);
    }

    /**
     * Embed a single string. Returns a float array.
     */
    public function embed(string $text): array
    {
        return $this->embedBatch([$text])[0] ?? [];
    }

    /**
     * Embed multiple strings efficiently with fallback if primary provider fails.
     * Guaranteed to return vector arrays (falls back to Local TF-IDF Vectorizer).
     */
    public function embedBatch(array $texts): array
    {
        if (empty($texts)) return [];

        $providersToTry = array_unique([
            $this->provider,
            'openrouter',
            'openai',
            'gemini',
            'ollama',
        ]);

        foreach ($providersToTry as $prov) {
            try {
                $model = $this->resolveModel($prov);

                $result = match ($prov) {
                    'openai'     => $this->embedBatchOpenAI($texts, $model),
                    'openrouter' => $this->embedBatchOpenRouter($texts, $model),
                    'gemini'     => array_map(fn($t) => $this->embedGemini($t, $model), $texts),
                    'ollama'     => array_map(fn($t) => $this->embedOllama($t, $model), $texts),
                    default      => null,
                };

                if (!empty($result) && !empty($result[0])) {
                    $this->provider = $prov;
                    $this->model    = $model;
                    return $result;
                }
            } catch (\Throwable $e) {
                Log::info("Embedding fallback notice for [{$prov}]: " . $e->getMessage());
            }
        }

        // Guaranteed fallback: Built-in Local Hashed TF-IDF Vectorizer
        $this->provider = 'local-tfidf';
        $this->model    = 'builtin-256d';

        return array_map(fn($t) => $this->embedLocalTfidf($t), $texts);
    }

    /**
     * Compute cosine similarity between two vectors (0.0 – 1.0).
     */
    public static function cosineSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b) || count($a) !== count($b)) return 0.0;

        $dot  = 0.0;
        $magA = 0.0;
        $magB = 0.0;

        foreach ($a as $i => $val) {
            $dot  += $val * $b[$i];
            $magA += $val * $val;
            $magB += $b[$i] * $b[$i];
        }

        $magA = sqrt($magA);
        $magB = sqrt($magB);

        return ($magA > 0 && $magB > 0) ? $dot / ($magA * $magB) : 0.0;
    }

    public function getProvider(): string { return $this->provider; }
    public function getModel(): string    { return $this->model;    }

    // ===== OPENAI =====
    private function embedBatchOpenAI(array $texts, string $model): array
    {
        $apiKey = Setting::get('openai_api_key');
        if (!$apiKey) throw new \RuntimeException('OpenAI API key missing.');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/embeddings', [
            'model' => $model,
            'input' => $texts,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenAI embedding error: ' . ($response->json('error.message') ?? $response->body()));
        }

        return array_column($response->json('data', []), 'embedding');
    }

    // ===== OPENROUTER =====
    private function embedBatchOpenRouter(array $texts, string $model): array
    {
        $apiKey = Setting::get('openrouter_api_key');
        if (!$apiKey) throw new \RuntimeException('OpenRouter API key missing.');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => config('app.url', 'http://localhost:8015'),
            'X-Title'       => 'AI Chat UI',
        ])->timeout(60)->post('https://openrouter.ai/api/v1/embeddings', [
            'model' => $model,
            'input' => $texts,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenRouter embedding error: ' . ($response->json('error.message') ?? $response->body()));
        }

        return array_column($response->json('data', []), 'embedding');
    }

    // ===== GEMINI =====
    private function embedGemini(string $text, string $model): array
    {
        $apiKey = Setting::get('gemini_api_key');
        if (!$apiKey) throw new \RuntimeException('Gemini API key missing.');

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:embedContent?key={$apiKey}";

        $response = Http::timeout(30)->post($url, [
            'model'   => "models/{$model}",
            'content' => ['parts' => [['text' => $text]]],
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini embedding error: ' . ($response->json('error.message') ?? $response->body()));
        }

        return $response->json('embedding.values', []);
    }

    // ===== OLLAMA =====
    private function embedOllama(string $text, string $model): array
    {
        $baseUrl = rtrim(Setting::get('ollama_url', 'http://host.docker.internal:11434'), '/');

        $response = Http::timeout(60)->post($baseUrl . '/api/embeddings', [
            'model'  => $model,
            'prompt' => $text,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Ollama embedding error: ' . $response->body());
        }

        return $response->json('embedding', []);
    }

    // ===== BUILT-IN LOCAL FEATURE VECTORIZER =====
    /**
     * Deterministic 256-dimensional feature hashing vectorizer for zero-dependency local embeddings.
     */
    private function embedLocalTfidf(string $text): array
    {
        $vector = array_fill(0, self::LOCAL_VECTOR_DIM, 0.0);
        $clean  = strtolower(preg_replace('/[^\w\s]/u', ' ', $text));
        $words  = array_filter(explode(' ', $clean), fn($w) => strlen($w) > 1);

        if (empty($words)) return $vector;

        foreach ($words as $word) {
            // Word hash
            $idx = abs(crc32($word)) % self::LOCAL_VECTOR_DIM;
            $vector[$idx] += 1.0;

            // Character 3-grams for fuzzy matching
            $len = mb_strlen($word);
            for ($i = 0; $i < $len - 2; $i++) {
                $ngram = mb_substr($word, $i, 3);
                $nidx  = abs(crc32($ngram)) % self::LOCAL_VECTOR_DIM;
                $vector[$nidx] += 0.5;
            }
        }

        // L2 Normalization
        $sumSq = 0.0;
        foreach ($vector as $val) {
            $sumSq += $val * $val;
        }

        if ($sumSq > 0) {
            $norm = sqrt($sumSq);
            for ($i = 0; $i < self::LOCAL_VECTOR_DIM; $i++) {
                $vector[$i] = round($vector[$i] / $norm, 6);
            }
        }

        return $vector;
    }

    // ===== HELPERS =====
    private function resolveProvider(string $chatProvider): string
    {
        if ($chatProvider === 'openrouter' && Setting::get('openrouter_api_key')) return 'openrouter';
        if ($chatProvider === 'openai' && Setting::get('openai_api_key'))         return 'openai';
        if ($chatProvider === 'gemini' && Setting::get('gemini_api_key'))         return 'gemini';
        if ($chatProvider === 'ollama')                                            return 'ollama';

        if (Setting::get('openrouter_api_key')) return 'openrouter';
        if (Setting::get('openai_api_key'))     return 'openai';
        if (Setting::get('gemini_api_key'))     return 'gemini';

        return 'ollama';
    }

    private function resolveModel(string $provider): string
    {
        return match ($provider) {
            'openai'     => 'text-embedding-3-small',
            'openrouter' => 'openai/text-embedding-3-small',
            'gemini'     => 'text-embedding-004',
            'ollama'     => 'nomic-embed-text',
            default      => 'text-embedding-3-small',
        };
    }
}
