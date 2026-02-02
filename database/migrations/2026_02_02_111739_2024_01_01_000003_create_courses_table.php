<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 200);
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('description_longue')->nullable();
            $table->foreignId('categorie_id')
                  ->constrained('course_categories')
                  ->onDelete('cascade');
            $table->foreignId('instructor_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->enum('niveau', ['debutant', 'intermediaire', 'avance', 'expert']);
            $table->integer('duree');
            $table->decimal('prix', 10, 2);
            $table->decimal('prix_promotion', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->string('video_presentation')->nullable();
            $table->json('objectifs')->nullable();
            $table->json('prerequis')->nullable();
            $table->json('public_cible')->nullable();
            $table->boolean('certification')->default(false);
            $table->string('langue', 2)->default('fr');
            $table->enum('statut', ['brouillon', 'actif', 'archive'])->default('brouillon');
            $table->dateTime('date_debut')->nullable();
            $table->dateTime('date_fin')->nullable();
            $table->integer('places_disponibles')->default(0);
            $table->integer('places_limite')->nullable();
            $table->integer('ordre')->default(0);
            $table->string('meta_titre')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};