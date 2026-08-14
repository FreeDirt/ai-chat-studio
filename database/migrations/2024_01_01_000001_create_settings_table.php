<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        DB::table('settings')->insert([
            ['key' => 'ai_provider',       'value' => 'openai',                       'created_at' => now(), 'updated_at' => now()],
            ['key' => 'openai_api_key',    'value' => '',                             'created_at' => now(), 'updated_at' => now()],
            ['key' => 'openai_model',      'value' => 'gpt-4o-mini',                  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'ollama_url',        'value' => 'http://host.docker.internal:11434', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'ollama_model',      'value' => 'llama3',                       'created_at' => now(), 'updated_at' => now()],
            ['key' => 'system_prompt',     'value' => 'You are a helpful assistant.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'temperature',       'value' => '0.7',                          'created_at' => now(), 'updated_at' => now()],
            ['key' => 'max_tokens',        'value' => '2048',                         'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
