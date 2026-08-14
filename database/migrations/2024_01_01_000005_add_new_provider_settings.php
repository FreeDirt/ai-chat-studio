<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $newSettings = [
            // OpenRouter
            ['key' => 'openrouter_api_key', 'value' => ''],
            ['key' => 'openrouter_model',   'value' => 'openai/gpt-4o-mini'],

            // Claude (Anthropic)
            ['key' => 'claude_api_key',     'value' => ''],
            ['key' => 'claude_model',       'value' => 'claude-3-5-sonnet-20241022'],

            // Gemini (Google)
            ['key' => 'gemini_api_key',     'value' => ''],
            ['key' => 'gemini_model',       'value' => 'gemini-2.0-flash'],
        ];

        foreach ($newSettings as $setting) {
            DB::table('settings')->insertOrIgnore(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'openrouter_api_key', 'openrouter_model',
            'claude_api_key',     'claude_model',
            'gemini_api_key',     'gemini_model',
        ])->delete();
    }
};
