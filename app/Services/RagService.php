<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class RagService
{
    public function __construct(
        private EmbeddingService     $embedder,
        private DocumentParserService $parser,
    ) {}

    /**
     * Process an uploaded file: parse → chunk → embed → store.
     * Updates Document status throughout.
     */
    public function processDocument(Document $document, string $filePath): void
    {
        try {
            $document->update(['status' => 'processing']);

            // 1. Parse text from file
            $uploadedFile = new \Illuminate\Http\File($filePath);
            $fakeUpload   = new \Illuminate\Http\UploadedFile(
                $filePath,
                $document->original_name,
                $document->mime_type,
                null,
                true
            );
            $text = $this->parser->extractText($fakeUpload);

            if (empty(trim($text))) {
                throw new \RuntimeException('No text could be extracted from this file.');
            }

            // 2. Chunk the text
            $chunkSize    = (int) Setting::get('rag_chunk_size', 1000);
            $chunkOverlap = (int) Setting::get('rag_chunk_overlap', 200);
            $chunks       = $this->parser->chunkText($text, $chunkSize, $chunkOverlap);

            if (empty($chunks)) {
                throw new \RuntimeException('Document produced no usable chunks.');
            }

            // 3. Embed all chunks (batch where supported)
            $embeddings = $this->embedder->embedBatch($chunks);

            // 4. Store chunks + embeddings
            DocumentChunk::where('document_id', $document->id)->delete();

            $rows = [];
            foreach ($chunks as $i => $chunk) {
                $rows[] = [
                    'document_id'    => $document->id,
                    'chunk_index'    => $i,
                    'content'        => $chunk,
                    'embedding'      => json_encode($embeddings[$i] ?? []),
                    'token_estimate' => (int) (mb_strlen($chunk) / 4), // rough estimate
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }

            // Insert in batches of 50
            foreach (array_chunk($rows, 50) as $batch) {
                DocumentChunk::insert($batch);
            }

            $document->update([
                'status'      => 'ready',
                'chunk_count' => count($chunks),
            ]);

        } catch (\Throwable $e) {
            Log::error("RAG processing failed for document #{$document->id}: " . $e->getMessage());
            $document->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Find the most relevant document chunks for a given query.
     * Searches both conversation documents AND persona documents (if personaId given).
     */
    public function retrieveRelevantChunks(string $query, Conversation $conversation, ?int $topK = null, ?int $personaId = null): array
    {
        $topK = $topK ?? (int) Setting::get('rag_top_k', 5);

        // Collect document IDs from conversation + persona
        $convDocIds    = Document::where('conversation_id', $conversation->id)
            ->where('status', 'ready')->pluck('id');

        $personaDocIds = $personaId
            ? Document::where('persona_id', $personaId)->where('status', 'ready')->pluck('id')
            : collect();

        $allDocIds = $convDocIds->merge($personaDocIds)->unique();

        if ($allDocIds->isEmpty()) return [];

        // Embed the query
        $queryVector = $this->embedder->embed($query);
        if (empty($queryVector)) return [];

        // Load all chunks
        $chunks = DocumentChunk::whereIn('document_id', $allDocIds)
            ->whereNotNull('embedding')
            ->with('document:id,original_name,persona_id')
            ->get();

        // Score each chunk
        $scored = $chunks->map(function ($chunk) use ($queryVector) {
            $vector = $chunk->embedding_vector;
            $score  = empty($vector) ? 0.0 : EmbeddingService::cosineSimilarity($queryVector, $vector);
            $source = $chunk->document->persona_id
                ? '🎭 ' . $chunk->document->original_name
                : '📎 ' . $chunk->document->original_name;

            return [
                'content'  => $chunk->content,
                'score'    => $score,
                'document' => $source,
                'chunk_id' => $chunk->id,
            ];
        })->filter(fn($c) => $c['score'] > 0.25)
          ->sortByDesc('score')
          ->take($topK)
          ->values()
          ->toArray();

        return $scored;
    }


    /**
     * Build a context block to inject into the system prompt.
     */
    public function buildContextBlock(array $chunks): string
    {
        if (empty($chunks)) return '';

        $context  = "---\n";
        $context .= "DOCUMENT CONTEXT (use this to answer the user's question):\n\n";

        foreach ($chunks as $i => $chunk) {
            $context .= "[Source: {$chunk['document']} | Relevance: " . round($chunk['score'] * 100) . "%]\n";
            $context .= $chunk['content'] . "\n\n";
        }

        $context .= "---\n";
        $context .= "If the answer is not found in the context above, say so clearly.\n";

        return $context;
    }

    /**
     * Inject context block into existing system prompt.
     */
    public function injectContextIntoPrompt(string $prompt, array $chunks): string
    {
        $contextBlock = $this->buildContextBlock($chunks);
        return rtrim($prompt) . "\n\n" . $contextBlock;
    }

    /**
     * Check if a conversation has any ready documents.
     */
    public function hasDocuments(Conversation $conversation): bool
    {
        return Document::where('conversation_id', $conversation->id)
            ->where('status', 'ready')
            ->exists();
    }
}
