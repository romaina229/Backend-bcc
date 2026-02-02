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
        Schema::create('forum_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('title');     $table->text('content');
            $table->boolean('is_pinned')->default(false);     $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        Schema::create('forum_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained();     $table->foreignId('user_id')->constrained();
            $table->text('content');
            $table->boolean('is_answer')->default(false); // Marquer comme réponse
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_threads');
        Schema::dropIfExists('forum_replie');
    }
};
