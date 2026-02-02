// 2024_01_01_000004_create_quizzes_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizzesTable extends Migration
{
    public function up()
    {
        Schema::create('quizz', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 200);
            $table->text('description')->nullable();

            // Référence à courses
            $table->foreignId('cours_id')
                  ->constrained('courses')
                  ->onDelete('cascade');

            // Référence à modules (nullable)
            $table->foreignId('module_id')
                  ->nullable()
                  ->constrained('modules')
                  ->onDelete('cascade');

            $table->enum('type', ['semaine', 'module', 'final'])->default('semaine');
            $table->integer('semaine')->nullable();
            $table->integer('duree')->nullable(); // en minutes
            $table->integer('note_minimale')->default(70);
            $table->integer('max_tentatives')->nullable();
            $table->dateTime('date_debut')->nullable();
            $table->dateTime('date_fin')->nullable();
            $table->enum('statut', ['brouillon', 'actif', 'termine'])->default('brouillon');
            $table->json('instructions')->nullable();
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quizz');
    }
}
