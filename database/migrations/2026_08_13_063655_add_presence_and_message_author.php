<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('remember_token');
            $table->foreignId('active_conversation_id')->nullable()->constrained('conversations')->onDelete('set null')->after('last_seen_at');
            $table->boolean('is_typing')->default(false)->after('active_conversation_id');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->after('conversation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['active_conversation_id']);
            $table->dropColumn(['last_seen_at', 'active_conversation_id', 'is_typing']);
        });
    }
};
