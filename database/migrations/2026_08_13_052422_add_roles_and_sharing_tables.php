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
            $table->string('role')->default('member')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
            $table->integer('token_quota')->nullable()->after('is_active');
            $table->string('avatar')->nullable()->after('token_quota');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->after('id');
            $table->enum('visibility', ['private', 'team', 'link', 'custom'])->default('private')->after('title');
            $table->string('share_token', 64)->nullable()->unique()->after('visibility');
        });

        Schema::create('conversation_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('permission', ['view', 'edit'])->default('view');
            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_shares');

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'visibility', 'share_token']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active', 'token_quota', 'avatar']);
        });
    }
};
