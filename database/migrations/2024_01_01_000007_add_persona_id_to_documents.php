<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('persona_id')
                  ->nullable()
                  ->after('conversation_id')
                  ->constrained()
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->dropColumn('persona_id');
        });
    }
};
