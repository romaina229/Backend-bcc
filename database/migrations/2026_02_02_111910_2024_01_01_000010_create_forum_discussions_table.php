<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_discussions', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 200);
            $table->string('slug')->unique();
            $table->longText('contenu');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('categorie_id')->constrained('forum_categories')->onDelete('cascade');
            $table->foreignId('cours_id')->nullable()->constrained('courses')->onDelete('cascade');
            $table->enum('statut', ['actif', 'ferme', 'archive'])->default('actif');
            $table->boolean('est_epingle')->default(false);
            $table->boolean('est_verrouille')->default(false);
            $table->integer('nombre_vues')->default(0);
            $table->unsignedBigInteger('dernier_post_id')->nullable(); // Pas de contrainte ici
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_discussions');
    }
};