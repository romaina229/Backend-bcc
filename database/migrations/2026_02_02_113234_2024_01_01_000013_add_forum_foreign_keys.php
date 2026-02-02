<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter la colonne et contrainte dans forum_categories
        Schema::table('forum_categories', function (Blueprint $table) {
            $table->foreignId('dernier_post_id')
                  ->nullable()
                  ->constrained('forum_posts')
                  ->onDelete('set null')
                  ->after('description');
        });
        
        // Ajouter la contrainte dans forum_discussions
        Schema::table('forum_discussions', function (Blueprint $table) {
            $table->foreign('dernier_post_id')
                  ->references('id')
                  ->on('forum_posts')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('forum_discussions', function (Blueprint $table) {
            $table->dropForeign(['dernier_post_id']);
        });
        
        Schema::table('forum_categories', function (Blueprint $table) {
            $table->dropForeign(['dernier_post_id']);
            $table->dropColumn('dernier_post_id');
        });
    }
};