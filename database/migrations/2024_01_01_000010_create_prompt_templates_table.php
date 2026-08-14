<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('shortcut')->nullable();
            $table->text('content');
            $table->string('category')->default('General');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prompt_templates');
    }
};
