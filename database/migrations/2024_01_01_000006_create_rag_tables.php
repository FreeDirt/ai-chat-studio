<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->nullable()->constrained()->onDelete('set null');
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->integer('chunk_count')->default(0);
            $table->enum('status', ['processing', 'ready', 'failed'])->default('processing');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('chunk_index');
            $table->longText('content');
            $table->longText('embedding')->nullable(); // JSON-encoded float array
            $table->unsignedInteger('token_estimate')->default(0);
            $table->timestamps();

            $table->index(['document_id', 'chunk_index']);
        });

        // New settings for RAG
        $newSettings = [
            ['key' => 'embedding_provider', 'value' => 'auto'],   // auto|openai|gemini|ollama
            ['key' => 'embedding_model',    'value' => 'auto'],    // auto or specific model name
            ['key' => 'rag_chunk_size',     'value' => '1000'],    // characters per chunk
            ['key' => 'rag_chunk_overlap',  'value' => '200'],     // overlap characters
            ['key' => 'rag_top_k',          'value' => '5'],       // top chunks to retrieve
        ];

        foreach ($newSettings as $s) {
            \DB::table('settings')->insertOrIgnore(array_merge($s, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_chunks');
        Schema::dropIfExists('documents');
        \DB::table('settings')->whereIn('key', [
            'embedding_provider', 'embedding_model',
            'rag_chunk_size', 'rag_chunk_overlap', 'rag_top_k',
        ])->delete();
    }
};
