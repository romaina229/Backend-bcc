<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quizz')->onDelete('cascade');
            $table->float('score')->default(0);
            $table->integer('temps_passe')->nullable();
            $table->json('reponses');
            $table->enum('statut', ['reussi', 'echoue'])->default('echoue');
            $table->timestamp('date_soumission')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};