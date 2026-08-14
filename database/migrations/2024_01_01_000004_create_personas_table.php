<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('🤖');
            $table->text('system_prompt');
            $table->string('color')->default('#6c63ff');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default personas
        DB::table('personas')->insert([
            [
                'name'          => 'Developer',
                'icon'          => '👨‍💻',
                'system_prompt' => 'You are an expert software developer. Provide concise, well-commented code examples. Prefer modern best practices. Always explain your reasoning.',
                'color'         => '#6c63ff',
                'is_active'     => true,
                'sort_order'    => 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name'          => 'Writer',
                'icon'          => '✍️',
                'system_prompt' => 'You are a professional content writer and editor. Help craft engaging, clear, and compelling written content. Offer suggestions to improve tone, style, and structure.',
                'color'         => '#e91e8c',
                'is_active'     => true,
                'sort_order'    => 2,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name'          => 'Travel Planner',
                'icon'          => '🌍',
                'system_prompt' => 'You are a world-class travel planner with extensive knowledge of destinations, cultures, and logistics. Provide detailed, practical travel advice including tips on accommodation, transportation, food, and must-see attractions.',
                'color'         => '#00bcd4',
                'is_active'     => true,
                'sort_order'    => 3,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name'          => 'Data Analyst',
                'icon'          => '📊',
                'system_prompt' => 'You are a senior data analyst. Help interpret data, suggest analytical approaches, write SQL queries, and explain statistical concepts clearly. Always recommend visualizations when appropriate.',
                'color'         => '#ff9800',
                'is_active'     => true,
                'sort_order'    => 4,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name'          => 'Marketing Expert',
                'icon'          => '📣',
                'system_prompt' => 'You are a seasoned digital marketing expert. Help craft marketing strategies, write copy, analyze campaigns, and provide actionable growth advice for businesses of all sizes.',
                'color'         => '#4caf50',
                'is_active'     => true,
                'sort_order'    => 5,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
