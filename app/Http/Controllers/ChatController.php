<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Persona;
use App\Models\Setting;
use App\Services\OpenAIService;
use App\Services\OllamaService;
use App\Services\OpenRouterService;
use App\Services\ClaudeService;
use App\Services\GeminiService;
use App\Services\RagService;
use App\Services\EmbeddingService;
use App\Services\DocumentParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Main chat page — load or create conversation.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        // My Personal Chats
        $myConversations = Conversation::where('user_id', $userId)
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_active_at')
            ->withCount('messages')
            ->get();

        // Shared Team Chats
        $sharedConversations = Conversation::where('user_id', '!=', $userId)
            ->where(function ($q) use ($userId) {
                $q->where('visibility', 'team')
                  ->orWhereHas('shares', fn($s) => $s->where('user_id', $userId));
            })
            ->orderByDesc('last_active_at')
            ->withCount('messages')
            ->get();

        $personas = Persona::active()->get();
        $provider = Setting::get('ai_provider', 'openai');

        $conversationId = $request->query('conversation');
        $conversation   = $conversationId
            ? Conversation::find($conversationId)
            : ($myConversations->first() ?? $sharedConversations->first());

        $userPermission = 'owner';
        if ($conversation) {
            if ($conversation->user_id === auth()->id() || auth()->user()->isSuperAdmin()) {
                $userPermission = 'owner';
            } else {
                $share = \App\Models\ConversationShare::where('conversation_id', $conversation->id)
                    ->where('user_id', auth()->id())
                    ->first();
                if ($share) {
                    $userPermission = $share->permission;
                } else if ($conversation->visibility === 'team') {
                    $userPermission = 'view';
                }
            }
        }

        $messages = $conversation ? $conversation->messages : collect();

        return view('chat.index', compact('myConversations', 'sharedConversations', 'personas', 'provider', 'conversation', 'messages', 'userPermission'));
    }

    /**
     * Search conversations by title or message content.
     */
    public function search(Request $request)
    {
        $q = trim($request->query('q', ''));
        if (empty($q)) {
            return response()->json(['results' => []]);
        }

        // 1. Match conversation titles
        $convMatches = Conversation::where('title', 'like', "%{$q}%")
            ->orderByDesc('last_active_at')
            ->limit(10)
            ->get()
            ->map(fn($c) => [
                'id'            => $c->id,
                'title'         => $c->title,
                'snippet'       => 'Title match',
                'match_type'    => 'title',
                'provider'      => $c->provider,
                'model'         => $c->model,
                'updated_at'    => $c->updated_at->diffForHumans(),
            ]);

        // 2. Match message content
        $msgMatches = Message::where('content', 'like', "%{$q}%")
            ->with('conversation')
            ->latest()
            ->limit(25)
            ->get()
            ->filter(fn($m) => $m->conversation !== null)
            ->map(function ($m) use ($q) {
                $cleanText = trim(preg_replace('/\s+/', ' ', strip_tags($m->content)));
                $pos = mb_stripos($cleanText, $q);
                if ($pos === false) $pos = 0;
                $start  = max(0, $pos - 35);
                $length = mb_strlen($q) + 70;
                $snippet = ($start > 0 ? '...' : '') . mb_substr($cleanText, $start, $length) . ($start + $length < mb_strlen($cleanText) ? '...' : '');

                return [
                    'id'            => $m->conversation->id,
                    'title'         => $m->conversation->title,
                    'snippet'       => $snippet,
                    'match_type'    => $m->role === 'user' ? 'user message' : 'AI reply',
                    'provider'      => $m->conversation->provider,
                    'model'         => $m->conversation->model,
                    'updated_at'    => $m->created_at->diffForHumans(),
                ];
            });

        // Merge results and deduplicate by conversation ID
        $results = $convMatches->concat($msgMatches)->unique('id')->values()->take(12);

        return response()->json(['results' => $results]);
    }


    /**
     * Create a new conversation.
     */
    public function newConversation()
    {
        $conversation = Conversation::create([
            'user_id'        => auth()->id(),
            'title'          => 'New Conversation',
            'provider'       => Setting::get('ai_provider', 'openai'),
            'model'          => match(Setting::get('ai_provider', 'openai')) {
                'openai'      => Setting::get('openai_model',      'gpt-4o-mini'),
                'openrouter'  => Setting::get('openrouter_model',  'openai/gpt-4o-mini'),
                'claude'      => Setting::get('claude_model',      'claude-3-5-sonnet-20241022'),
                'gemini'      => Setting::get('gemini_model',      'gemini-2.0-flash'),
                default       => Setting::get('ollama_model',      'llama3'),
            },
            'visibility'     => 'private',
            'last_active_at' => now(),
        ]);

        return response()->json(['conversation' => $conversation]);
    }

    /**
     * Send a message and get an AI reply.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message'         => 'required|string|max:32768',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);
        $this->verifyCanEdit($conversation);
        auth()->user()?->update(['is_typing' => false]);

        // Save user message
        Message::create([
            'conversation_id' => $conversation->id,
            'user_id'         => auth()->id(),
            'role'            => 'user',
            'content'         => $request->message,
        ]);

        // Auto-title the conversation from the first message
        if ($conversation->messages()->count() === 1) {
            $conversation->update([
                'title' => Conversation::generateTitle($request->message),
            ]);
        }

        $personaId = $request->filled('persona_id') ? (int) $request->persona_id : null;

        return $this->generateAiReply($conversation, $personaId);
    }

    /**
     * Real-time SSE Streaming AI response (word-by-word typewriter).
     */
    public function streamMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message'         => 'required|string|max:32768',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);
        $this->verifyCanEdit($conversation);

        // Save user message
        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'user_id'         => auth()->id(),
            'role'            => 'user',
            'content'         => $request->message,
        ]);

        // Auto-title conversation
        if ($conversation->messages()->count() === 1) {
            $conversation->update([
                'title' => Conversation::generateTitle($request->message),
            ]);
        }

        $personaId = $request->filled('persona_id') ? (int) $request->persona_id : null;

        // System prompt & RAG context resolution
        $systemPromptOverride = null;
        $ragChunks = [];
        $ragUsed = false;

        if ($personaId) {
            $persona = Persona::find($personaId);
            if ($persona) {
                $systemPromptOverride = self::resolveVariables($persona->system_prompt);
                $ragService = app(\App\Services\RagService::class);
                $ragChunks = $ragService->retrieveRelevantChunks($request->message, $conversation, null, $personaId);
            }
        } else {
            $ragService = app(\App\Services\RagService::class);
            $ragChunks = $ragService->retrieveRelevantChunks($request->message, $conversation);
        }

        if (!empty($ragChunks)) {
            $ragUsed = true;
            $systemPromptOverride = app(\App\Services\RagService::class)->injectContextIntoPrompt(
                $systemPromptOverride ?: Setting::get('system_prompt', 'You are a helpful AI assistant.'),
                $ragChunks
            );
        }

        $messages = $conversation->messages()->orderBy('id')->get()->map(fn($m) => [
            'role'    => $m->role,
            'content' => $m->content,
        ])->toArray();

        return response()->stream(function () use ($messages, $systemPromptOverride, $conversation, $ragUsed, $ragChunks, $userMessage) {
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
            if (ob_get_level() > 0) @ob_end_clean();

            $streamService = app(\App\Services\StreamService::class);

            $result = $streamService->stream($messages, $systemPromptOverride, function ($chunk) {
                echo "data: " . json_encode(['chunk' => $chunk]) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
            });

            // Save assistant message to MariaDB
            $aiMessage = Message::create([
                'conversation_id'  => $conversation->id,
                'role'             => 'assistant',
                'content'          => $result['content'],
                'model'            => $result['model'],
                'tokens_used'      => $result['tokens_used'] ?? null,
                'response_time_ms' => $result['response_time_ms'],
            ]);

            $conversation->update([
                'last_active_at' => now(),
                'provider'       => Setting::get('ai_provider', 'openai'),
                'model'          => $result['model'],
            ]);

            $ragSources = $ragUsed ? array_map(fn($c) => [
                'document' => $c['document'],
                'score'    => round($c['score'] * 100),
            ], $ragChunks) : [];

            echo "data: " . json_encode([
                'done'            => true,
                'content'         => $result['content'],
                'model'           => $result['model'],
                'tokens'          => $result['tokens_used'] ?? null,
                'time_ms'         => $result['response_time_ms'],
                'message_id'      => $aiMessage->id,
                'user_message_id' => $userMessage->id,
                'title'           => $conversation->fresh()->title,
                'rag_used'        => $ragUsed,
                'rag_sources'     => $ragSources,
            ]) . "\n\n";

            if (ob_get_level() > 0) ob_flush();
            flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Edit a sent user message — truncates conversation history and generates a new AI reply.
     */
    public function editMessage(Request $request, Message $message)
    {
        $request->validate(['content' => 'required|string|max:32768']);

        if ($message->role !== 'user') {
            return response()->json(['success' => false, 'error' => 'Only user messages can be edited.'], 400);
        }

        $conversation = $message->conversation;

        // Delete all messages created after or alongside this user message ID
        Message::where('conversation_id', $conversation->id)
            ->where('id', '>', $message->id)
            ->delete();

        // Update the user message content
        $message->update(['content' => $request->content]);

        $personaId = $request->filled('persona_id') ? (int) $request->persona_id : null;

        return $this->generateAiReply($conversation, $personaId);
    }

    /**
     * Regenerate AI response — deletes last assistant reply and generates a new AI reply.
     */
    public function regenerateMessage(Request $request, Conversation $conversation)
    {
        $lastAssistantMsg = $conversation->messages()->where('role', 'assistant')->latest()->first();

        if ($lastAssistantMsg) {
            $lastAssistantMsg->delete();
        }

        $lastUserMsg = $conversation->messages()->where('role', 'user')->latest()->first();

        if (!$lastUserMsg) {
            return response()->json(['success' => false, 'error' => 'No user message to regenerate.'], 400);
        }

        $personaId = $request->filled('persona_id') ? (int) $request->persona_id : null;

        return $this->generateAiReply($conversation, $personaId);
    }

    /**
     * Delete a single message.
     */
    public function deleteMessage(Message $message)
    {
        $message->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Delete a conversation and all its messages.
     */
    public function deleteConversation(Conversation $conversation)
    {
        $conversation->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Rename a conversation.
     */
    public function renameConversation(Request $request, Conversation $conversation)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $conversation->update(['title' => $request->title]);
        return response()->json(['success' => true, 'title' => $conversation->title]);
    }

    /**
     * Toggle pin status.
     */
    public function togglePin(Conversation $conversation)
    {
        $conversation->update(['is_pinned' => !$conversation->is_pinned]);
        return response()->json(['success' => true, 'is_pinned' => $conversation->is_pinned]);
    }

    /**
     * Get all messages for a conversation.
     */
    public function getMessages(Conversation $conversation)
    {
        return response()->json([
            'conversation' => $conversation,
            'messages'     => $conversation->messages,
        ]);
    }

    /**
     * List available models from active provider.
     */
    public function listModels()
    {
        $provider = Setting::get('ai_provider', 'openai');

        try {
            $models = match($provider) {
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

    /**
     * Common AI reply generator for send, edit, and regenerate.
     */
    private function generateAiReply(Conversation $conversation, ?int $personaId = null)
    {
        // Build message history for context
        $history = $conversation->messages()
            ->where('role', '!=', 'system')
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $lastUserMessage = $conversation->messages()->where('role', 'user')->latest()->first()?->content ?? '';

        // Check for persona system prompt override
        $personaPrompt = null;
        if ($personaId) {
            $persona = Persona::find($personaId);
            if ($persona) {
                $personaPrompt = self::resolveVariables($persona->system_prompt);
            }
        }

        // === RAG: inject relevant document context ===
        $ragChunks            = [];
        $ragUsed              = false;
        $systemPromptOverride = $personaPrompt;

        try {
            $rag = new RagService(new EmbeddingService(), new DocumentParserService());
            $hasConvDocs    = $rag->hasDocuments($conversation);
            $hasPersonaDocs = $personaId && \App\Models\Document::where('persona_id', $personaId)
                                                ->where('status', 'ready')->exists();

            if (($hasConvDocs || $hasPersonaDocs) && $lastUserMessage) {
                $ragChunks = $rag->retrieveRelevantChunks(
                    $lastUserMessage, $conversation, null, $personaId
                );
                if (!empty($ragChunks)) {
                    $ragContext           = $rag->buildContextBlock($ragChunks);
                    $basePrompt           = $personaPrompt
                        ?? self::resolveVariables(Setting::get('system_prompt', 'You are a helpful assistant.'));
                    $systemPromptOverride = $basePrompt . "\n\n" . $ragContext;
                    $ragUsed              = true;
                }
            } elseif ($personaPrompt) {
                $systemPromptOverride = $personaPrompt;
            }
        } catch (\Throwable $e) {
            Log::warning('RAG retrieval skipped: ' . $e->getMessage());
        }

        try {
            $provider = Setting::get('ai_provider', 'openai');
            $result   = $this->callAI($provider, $history, $systemPromptOverride);

            // Save assistant reply
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'role'            => 'assistant',
                'content'         => $result['content'],
                'model'           => $result['model'],
                'tokens_used'     => $result['tokens_used'],
                'response_time_ms' => $result['response_time_ms'],
            ]);

            // Update conversation metadata
            $conversation->update([
                'last_active_at' => now(),
                'provider'       => $provider,
                'model'          => $result['model'],
            ]);

            return response()->json([
                'success'    => true,
                'reply'      => $result['content'],
                'model'      => $result['model'],
                'tokens'     => $result['tokens_used'],
                'time_ms'    => $result['response_time_ms'],
                'message_id' => $aiMessage->id,
                'title'      => $conversation->fresh()->title,
                'rag_used'   => $ragUsed,
                'rag_sources'=> $ragUsed ? array_map(fn($c) => [
                    'document' => $c['document'],
                    'score'    => round($c['score'] * 100),
                ], $ragChunks) : [],
            ]);

        } catch (\Throwable $e) {
            Log::error('AI Chat error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Route AI request to the correct service.
     */
    private function callAI(string $provider, array $messages, ?string $systemPromptOverride = null): array
    {
        $config = $systemPromptOverride ? ['system_prompt' => $systemPromptOverride] : [];

        return match ($provider) {
            'openai'     => (new OpenAIService($config))->chat($messages),
            'openrouter' => (new OpenRouterService($config))->chat($messages),
            'claude'     => (new ClaudeService($config))->chat($messages),
            'gemini'     => (new GeminiService($config))->chat($messages),
            'ollama'     => (new OllamaService($config))->chat($messages),
            default      => throw new \InvalidArgumentException("Unknown provider: $provider"),
        };
    }

    /**
     * Resolve dynamic variables in system prompts.
     */
    public static function resolveVariables(string $prompt): string
    {
        $now = now();

        $vars = [
            '{{date}}'     => $now->format('F j, Y'),
            '{{time}}'     => $now->format('g:i A'),
            '{{datetime}}' => $now->format('F j, Y g:i A'),
            '{{day}}'      => $now->format('l'),
            '{{month}}'    => $now->format('F'),
            '{{year}}'     => $now->format('Y'),
            '{{timezone}}' => $now->timezoneName,
        ];

        return str_replace(array_keys($vars), array_values($vars), $prompt);
    }

    /**
     * Compare Mode: Send prompt to 2 AI providers/models simultaneously and return benchmarked comparison.
     */
    public function compare(Request $request)
    {
        $request->validate([
            'message'          => 'required|string|max:32768',
            'provider_a'       => 'required|string',
            'model_a'          => 'nullable|string',
            'provider_b'       => 'required|string',
            'model_b'          => 'nullable|string',
            'persona_id'       => 'nullable|integer',
        ]);

        $prompt = $request->message;
        $personaId = $request->filled('persona_id') ? (int) $request->persona_id : null;

        // Build messages array
        $systemPrompt = "You are a helpful AI assistant.";
        if ($personaId) {
            $persona = Persona::find($personaId);
            if ($persona) {
                $systemPrompt = self::resolveVariables($persona->system_prompt);
            }
        } else {
            $globalPrompt = Setting::get('system_prompt', '');
            if ($globalPrompt) {
                $systemPrompt = self::resolveVariables($globalPrompt);
            }
        }

        $formattedMessages = [
            ['role' => 'user', 'content' => $prompt],
        ];

        // Call Provider A
        $resAData = null;
        $errorA = null;
        try {
            $resAData = $this->callAI($request->provider_a, $formattedMessages, $systemPrompt);
        } catch (\Throwable $e) {
            $errorA = $e->getMessage();
        }

        // Call Provider B
        $resBData = null;
        $errorB = null;
        try {
            $resBData = $this->callAI($request->provider_b, $formattedMessages, $systemPrompt);
        } catch (\Throwable $e) {
            $errorB = $e->getMessage();
        }

        return response()->json([
            'success' => true,
            'prompt'  => $prompt,
            'a'       => [
                'provider'    => $request->provider_a,
                'model'       => $resAData['model'] ?? $request->model_a,
                'content'     => $resAData['content'] ?? null,
                'error'       => $errorA,
                'time_ms'     => $resAData['response_time_ms'] ?? 0,
                'tokens'      => $resAData['tokens_used'] ?? null,
            ],
            'b'       => [
                'provider'    => $request->provider_b,
                'model'       => $resBData['model'] ?? $request->model_b,
                'content'     => $resBData['content'] ?? null,
                'error'       => $errorB,
                'time_ms'     => $resBData['response_time_ms'] ?? 0,
                'tokens'      => $resBData['tokens_used'] ?? null,
            ],
        ]);
    }

    /**
     * Export conversation history as Markdown (.md), Plain Text (.txt), or JSON (.json).
     */
    public function export(Request $request, Conversation $conversation)
    {
        $format = strtolower($request->query('format', 'md'));
        $conversation->load('messages');

        $slug = \Illuminate\Support\Str::slug($conversation->title ?: 'conversation');
        $date = now()->format('Y-m-d');

        if ($format === 'html' || $format === 'pdf') {
            $filename = "{$slug}-{$date}.html";
            $appName = \App\Models\Setting::get('app_name', 'AI Chat Studio');

            $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">';
            $html .= '<title>' . e($conversation->title) . ' - ' . e($appName) . '</title>';
            $html .= '<style>
                :root { --bg: #0f172a; --card: #1e293b; --accent: #6c63ff; --text: #f8fafc; --muted: #94a3b8; --border: #334155; }
                body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); padding: 40px 20px; margin: 0; max-width: 900px; margin: 0 auto; line-height: 1.6; }
                .header { border-bottom: 2px solid var(--border); padding-bottom: 20px; margin-bottom: 30px; }
                .title { font-size: 24px; font-weight: 800; color: #fff; margin: 0 0 8px; }
                .meta { font-size: 13px; color: var(--muted); display: flex; gap: 16px; flex-wrap: wrap; }
                .meta-badge { background: rgba(108,99,255,0.2); color: #a5b4fc; padding: 2px 8px; border-radius: 99px; font-weight: 600; }
                .msg { margin-bottom: 24px; background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 20px; }
                .msg-user { border-left: 4px solid var(--accent); }
                .msg-assistant { border-left: 4px solid #10b981; }
                .msg-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-weight: 700; font-size: 14px; }
                .msg-body { font-size: 14px; white-space: pre-wrap; word-break: break-word; }
                code { background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px; }
                pre { background: #020617; padding: 16px; border-radius: 8px; overflow-x: auto; border: 1px solid var(--border); }
                pre code { background: none; padding: 0; }
                @media print {
                    body { background: #fff !important; color: #000 !important; max-width: 100% !important; padding: 0 !important; }
                    .msg { background: #fff !important; color: #000 !important; border: 1px solid #ccc !important; page-break-inside: avoid; }
                    .meta-badge { background: #eee !important; color: #333 !important; }
                }
            </style></head><body>';

            $html .= '<div class="header">';
            $html .= '<h1 class="title">' . e($conversation->title) . '</h1>';
            $html .= '<div class="meta"><span>Provider: <span class="meta-badge">' . e($conversation->provider) . '</span></span>';
            $html .= '<span>Model: <span class="meta-badge">' . e($conversation->model) . '</span></span>';
            $html .= '<span>Exported: ' . now()->format('F j, Y g:i A') . '</span></div></div>';

            foreach ($conversation->messages as $m) {
                $isUser = $m->role === 'user';
                $class = $isUser ? 'msg-user' : 'msg-assistant';
                $author = $isUser ? '👤 User' : '🤖 Assistant';
                $time = $m->created_at->format('Y-m-d H:i:s');

                $html .= '<div class="msg ' . $class . '">';
                $html .= '<div class="msg-header"><span>' . $author . '</span><span style="font-size:12px;color:var(--muted)">' . $time . '</span></div>';
                $html .= '<div class="msg-body">' . e($m->content) . '</div>';
                $html .= '</div>';
            }

            if ($format === 'pdf') {
                $html .= '<script>window.onload = function() { window.print(); };</script>';
            }

            $html .= '</body></html>';

            $disposition = $format === 'pdf' ? 'inline' : 'attachment';

            return response($html, 200, [
                'Content-Type'        => 'text/html; charset=UTF-8',
                'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            ]);
        }

        if ($format === 'json') {
            $filename = "{$slug}-{$date}.json";
            $data = [
                'id'         => $conversation->id,
                'title'      => $conversation->title,
                'provider'   => $conversation->provider,
                'model'      => $conversation->model,
                'created_at' => $conversation->created_at->toIso8601String(),
                'messages'   => $conversation->messages->map(fn($m) => [
                    'id'           => $m->id,
                    'role'         => $m->role,
                    'content'      => $m->content,
                    'tokens'       => $m->tokens,
                    'response_ms'  => $m->response_ms,
                    'created_at'   => $m->created_at->toIso8601String(),
                ]),
            ];

            return response()->json($data, 200, [
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        if ($format === 'txt') {
            $filename = "{$slug}-{$date}.txt";
            $lines = [];
            $lines[] = "=== " . mb_strtoupper($conversation->title) . " ===";
            $lines[] = "Provider: {$conversation->provider} | Model: {$conversation->model}";
            $lines[] = "Exported: " . now()->format('Y-m-d H:i:s');
            $lines[] = str_repeat('-', 60);
            $lines[] = "";

            foreach ($conversation->messages as $m) {
                $sender = $m->role === 'user' ? 'USER' : 'ASSISTANT';
                $time = $m->created_at->format('Y-m-d H:i:s');
                $lines[] = "[{$sender}] ({$time})";
                $lines[] = $m->content;
                $lines[] = str_repeat('-', 40);
                $lines[] = "";
            }

            return response(implode("\n", $lines), 200, [
                'Content-Type'        => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // Default Markdown (.md)
        $filename = "{$slug}-{$date}.md";
        $md = [];
        $md[] = "# " . $conversation->title;
        $md[] = "> **Provider**: `" . e($conversation->provider) . "` | **Model**: `" . e($conversation->model) . "` | **Exported**: `" . now()->format('F j, Y g:i A') . "`";
        $md[] = "";
        $md[] = "---";
        $md[] = "";

        foreach ($conversation->messages as $m) {
            if ($m->role === 'user') {
                $md[] = "### 👤 User";
                $md[] = "*(" . $m->created_at->format('Y-m-d H:i:s') . ")*";
                $md[] = "";
                $md[] = $m->content;
                $md[] = "";
                $md[] = "---";
                $md[] = "";
            } else {
                $tokens = $m->tokens ? " · {$m->tokens} tokens" : "";
                $timeMs = $m->response_ms ? " · {$m->response_ms}ms" : "";
                $md[] = "### 🤖 Assistant";
                $md[] = "*(" . $m->created_at->format('Y-m-d H:i:s') . "{$timeMs}{$tokens})*";
                $md[] = "";
                $md[] = $m->content;
                $md[] = "";
                $md[] = "---";
                $md[] = "";
            }
        }

        return response(implode("\n", $md), 200, [
            'Content-Type'        => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Rewrite & enhance user input prompt using AI prompt engineering best practices.
     */
    public function enhancePrompt(Request $request)
    {
        $request->validate(['prompt' => 'required|string|max:8192']);
        $rawPrompt = trim($request->input('prompt'));

        $systemInstruction = "You are an expert AI Prompt Engineer. "
            . "Take the user's draft prompt and rewrite/expand it into a clear, detailed, highly structured, and effective AI prompt. "
            . "Include relevant context, role, constraints, formatting requirements, or step-by-step instructions where appropriate. "
            . "CRITICAL: Output ONLY the final enhanced prompt text directly without any introduction, meta-commentary, markdown wrapping, or explanations.";

        $messages = [
            ['role' => 'system', 'content' => $systemInstruction],
            ['role' => 'user', 'content' => "Draft prompt to enhance:\n" . $rawPrompt],
        ];

        try {
            $provider = Setting::get('ai_provider', 'openai');
            $result   = $this->callAI($provider, $messages, $systemInstruction);
            $enhanced = trim($result['content']);
            $enhanced = preg_replace('/^"|"$/', '', $enhanced);

            return response()->json([
                'success'         => true,
                'original_prompt' => $rawPrompt,
                'enhanced_prompt' => $enhanced,
                'model'           => $result['model'] ?? 'AI',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to enhance prompt: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get aggregate usage statistics (tokens, costs, latency, model breakdown).
     */
    public function analytics(Request $request)
    {
        $provider = strtolower(trim($request->input('provider', 'all')));

        $msgQuery = Message::query();

        if ($provider !== 'all' && !empty($provider)) {
            if ($provider === 'openrouter') {
                $msgQuery->where(function ($q) {
                    $q->where('model', 'LIKE', '%openrouter%')
                      ->orWhere('model', 'LIKE', '%:%');
                });
            } elseif ($provider === 'openai') {
                $msgQuery->where('model', 'LIKE', '%gpt%');
            } elseif ($provider === 'claude') {
                $msgQuery->where('model', 'LIKE', '%claude%');
            } elseif ($provider === 'gemini') {
                $msgQuery->where('model', 'LIKE', '%gemini%');
            } elseif ($provider === 'ollama') {
                $msgQuery->where(function ($q) {
                    $q->where('model', 'LIKE', '%ollama%')
                      ->orWhere('model', 'LIKE', '%llama%');
                });
            } else {
                $msgQuery->where('model', 'LIKE', "%{$provider}%");
            }
        }

        $totalConversations = Conversation::count();
        $totalMessages      = (clone $msgQuery)->count();
        $totalTokens        = (int) (clone $msgQuery)->sum('tokens_used');
        $avgMs              = (int) (clone $msgQuery)->whereNotNull('response_time_ms')->avg('response_time_ms');

        $costPer1k = match($provider) {
            'openai' => 0.0025,
            'claude' => 0.0030,
            'ollama' => 0.0000,
            default  => 0.00015,
        };
        $estimatedCost = round(($totalTokens / 1000) * $costPer1k, 4);

        $modelBreakdown = (clone $msgQuery)->whereNotNull('model')
            ->selectRaw('model, COUNT(*) as count, SUM(tokens_used) as tokens')
            ->groupBy('model')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'success'             => true,
            'provider'            => $provider,
            'total_conversations' => $totalConversations,
            'total_messages'      => number_format($totalMessages),
            'total_tokens'        => number_format($totalTokens),
            'raw_tokens'          => $totalTokens,
            'estimated_cost'      => '$' . number_format($estimatedCost, 4),
            'avg_latency_ms'      => $avgMs ? "{$avgMs}ms" : 'N/A',
            'models'              => $modelBreakdown,
        ]);
    }

    /**
     * Branch off an existing message (creates an alternative thread variant).
     */
    public function branchMessage(Request $request, Message $message)
    {
        $this->verifyCanEdit($message->conversation);

        $request->validate([
            'content' => 'nullable|string|max:10000',
        ]);

        $newContent = $request->content ?? $message->content;

        // Create a sibling message attached to the same parent
        $newBranch = Message::create([
            'conversation_id' => $message->conversation_id,
            'parent_id'       => $message->parent_id,
            'user_id'         => auth()->id(),
            'role'            => $message->role,
            'content'         => $newContent,
            'model'           => $message->model,
        ]);

        // Get sibling count and position
        $siblings = Message::where('conversation_id', $message->conversation_id)
            ->where('parent_id', $message->parent_id)
            ->orderBy('id')
            ->get();

        $position = $siblings->search(fn($item) => $item->id === $newBranch->id) + 1;

        return response()->json([
            'success'     => true,
            'message'     => $newBranch,
            'html'        => view('chat._message', ['msg' => $newBranch])->render(),
            'branch_info' => [
                'current' => $position,
                'total'   => $siblings->count(),
                'siblings'=> $siblings->pluck('id'),
            ]
        ]);
    }

    /**
     * Get branch navigation information (sibling variants).
     */
    public function getSiblings(Message $message)
    {
        $siblings = Message::where('conversation_id', $message->conversation_id)
            ->where('parent_id', $message->parent_id)
            ->orderBy('id')
            ->get();

        $position = $siblings->search(fn($item) => $item->id === $message->id) + 1;

        return response()->json([
            'success'  => true,
            'current'  => $position,
            'total'    => $siblings->count(),
            'siblings' => $siblings->map(fn($item) => [
                'id'       => $item->id,
                'role'     => $item->role,
                'snippet'  => \Illuminate\Support\Str::limit($item->content, 40),
                'active'   => $item->id === $message->id,
            ]),
        ]);
    }

    private function verifyCanEdit(Conversation $conversation)
    {
        if ($conversation->user_id === auth()->id() || auth()->user()->isSuperAdmin()) {
            return;
        }

        $share = \App\Models\ConversationShare::where('conversation_id', $conversation->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($share && $share->permission === 'edit') {
            return;
        }

        abort(403, 'Read-only access. You do not have permission to modify or send messages in this conversation.');
    }
}
