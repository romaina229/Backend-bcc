<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->longText('contenu');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('discussion_id')->constrained('forum_discussions')->onDelete('cascade');
            $table->boolean('est_premier_post')->default(false);
            $table->boolean('est_meilleure_reponse')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_posts');
    }
};