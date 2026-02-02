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
        Schema::create('cours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_id')->constrained();
            $table->string('title');
            $table->string('type'); // video, pdf, presentation, lien
            $table->text('content');
            $table->string('file_path')->nullable();
            $table->integer('duration_minutes');
            $table->integer('order');
            $table->timestamps();
        });
        Schema::create('quizze', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_id')->constrained();
            $table->string('title');
            $table->text('description');
            $table->integer('time_limit')->nullable(); // en minutes
            $table->integer('max_attempts')->default(1);     $table->float('passing_score')->default(70);     $table->boolean('shuffle_questions')->default(false);
            $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table) {     $table->id();     $table->foreignId('quiz_id')->constrained();
            $table->text('question');
            $table->enum('type', ['multiple', 'unique', 'texte']);
            $table->json('options')->nullable(); // Pour les QCM
            $table->string('correct_answer');
            $table->float('points')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cours');
        Schema::dropIfExists('quizze');
        Schema::dropIfExists('questions');
    }
};
