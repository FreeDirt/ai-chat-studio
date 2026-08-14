<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Document;
use App\Services\DocumentParserService;
use App\Services\EmbeddingService;
use App\Services\RagService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    private RagService $rag;

    public function __construct()
    {
        $this->rag = new RagService(new EmbeddingService(), new DocumentParserService());
    }

    /**
     * Upload and process a document for a conversation.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file'            => 'required|file|max:20480',
            'conversation_id' => 'nullable|exists:conversations,id',
            'persona_id'      => 'nullable|exists:personas,id',
        ]);

        if (!$request->filled('conversation_id') && !$request->filled('persona_id')) {
            return response()->json(['success' => false, 'error' => 'Provide conversation_id or persona_id.'], 422);
        }

        $file = $request->file('file');

        $allowedExts = ['pdf','docx','txt','md','php','js','ts','py','java','go',
                        'rb','cs','cpp','c','h','html','css','json','yaml','yml',
                        'xml','sh','sql','csv','env','jsx','tsx','vue','rs'];

        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, $allowedExts)) {
            return response()->json(['success' => false, 'error' => "File type .{$ext} is not supported."], 422);
        }

        // Store the file
        $storedName = Str::uuid() . '.' . $ext;
        $storedPath = $file->storeAs('documents', $storedName);
        $fullPath   = Storage::path($storedPath);

        // Create DB record
        $document = Document::create([
            'conversation_id' => $request->conversation_id ?: null,
            'persona_id'      => $request->persona_id ?: null,
            'original_name'   => $file->getClientOriginalName(),
            'stored_name'     => $storedName,
            'mime_type'       => $file->getMimeType(),
            'file_size'       => $file->getSize(),
            'status'          => 'processing',
        ]);

        // Process synchronously (for simplicity — can move to a Queue later)
        try {
            $this->rag->processDocument($document, $fullPath);

            return response()->json([
                'success'  => true,
                'document' => [
                    'id'           => $document->id,
                    'name'         => $document->original_name,
                    'icon'         => $document->icon,
                    'size'         => $document->file_size_human,
                    'chunk_count'  => $document->fresh()->chunk_count,
                    'status'       => 'ready',
                    'status_color' => '#4ade80',
                ],
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'document' => [
                    'id'     => $document->id,
                    'name'   => $document->original_name,
                    'status' => 'failed',
                ],
            ], 500);
        }
    }

    /**
     * List documents for a conversation.
     */
    public function list(Conversation $conversation)
    {
        $docs = $conversation->documents()->latest()->get()->map(fn($d) => $this->docToArray($d));
        return response()->json(['documents' => $docs]);
    }

    /**
     * List documents attached to a persona.
     */
    public function listForPersona(\App\Models\Persona $persona)
    {
        $docs = $persona->documents()->latest()->get()->map(fn($d) => $this->docToArray($d));
        return response()->json(['documents' => $docs]);
    }

    private function docToArray(Document $d): array
    {
        return [
            'id'           => $d->id,
            'name'         => $d->original_name,
            'icon'         => $d->icon,
            'size'         => $d->file_size_human,
            'chunk_count'  => $d->chunk_count,
            'status'       => $d->status,
            'status_color' => $d->status_color,
        ];
    }

    /**
     * Delete a document and its chunks.
     */
    public function destroy(Document $document)
    {
        // Delete stored file
        Storage::delete('documents/' . $document->stored_name);
        $document->delete(); // chunks cascade

        return response()->json(['success' => true]);
    }
}
