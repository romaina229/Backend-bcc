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
        // Créer d'abord les tables de base sans dépendances
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('image')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->integer('duration'); // en heures
            $table->enum('level', ['debutant', 'intermediaire', 'avance']);
            $table->json('objectifs')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('formateur_id')->constrained('users');
            $table->timestamps();
        });

        // Table quizzes (pour les références futures)
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration')->nullable(); // en minutes
            $table->integer('total_points')->default(100);
            $table->integer('passing_score')->default(70);
            $table->integer('max_attempts')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table assignments (pour les références futures)
        Schema::create('assignment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->decimal('max_points', 5, 2)->default(100);
            $table->dateTime('due_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table submissions (pour les références futures)
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['draft', 'submitted', 'graded'])->default('draft');
            $table->timestamps();
        });

        // Table questions (pour les références futures)
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->text('question');
            $table->enum('type', ['multiple_choice', 'true_false', 'short_answer']);
            $table->json('options')->nullable(); // Pour les questions à choix multiple
            $table->string('correct_answer');
            $table->float('points')->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Maintenant créer les tables qui dépendent des précédentes
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->foreignId('formation_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->float('final_score')->nullable();
            $table->enum('status', ['pending', 'issued', 'revoked'])->default('pending');
            $table->string('file_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('grading_rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->string('criterion');
            $table->text('description')->nullable();
            $table->float('max_points');
            $table->integer('order');
            $table->timestamps();
        });

        Schema::create('submission_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->onDelete('cascade');
            $table->foreignId('rubric_id')->constrained('grading_rubrics')->onDelete('cascade');
            $table->float('points_awarded');
            $table->text('feedback')->nullable();
            $table->foreignId('graded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->float('score')->default(0);
            $table->integer('time_taken')->nullable(); // en secondes
            $table->integer('correct_answers')->default(0);
            $table->integer('total_questions')->default(0);
            $table->json('user_answers'); // Stocker toutes les réponses
            $table->enum('status', ['in_progress', 'completed', 'expired'])->default('in_progress');
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('question_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('user_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->float('points_earned')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer dans l'ordre inverse de création
        Schema::dropIfExists('question_attempts');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('submission_grades');
        Schema::dropIfExists('grading_rubrics');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('formations');
    }
};
