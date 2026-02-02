<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 200);
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->enum('type', ['video', 'texte', 'quiz', 'ressource'])->default('video');
            $table->longText('contenu')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('duree')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('gratuit')->default(false);
            $table->boolean('actif')->default(true);
            $table->json('objectifs')->nullable();
            $table->json('ressources')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};