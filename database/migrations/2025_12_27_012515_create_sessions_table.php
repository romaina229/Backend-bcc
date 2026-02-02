<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('max_participants');
            $table->integer('current_participants')->default(0);
            $table->enum('status', ['planifie', 'en_cours', 'termine'])->default('planifie');
            $table->foreignId('formateur_id')->constrained('users');
            $table->foreignId('user_id')->constrained();
            // AJOUTEZ CES COLONNES POUR LARAVEL SESSIONS
            $table->longText('payload')->nullable(); // <-- IMPORTANT
            $table->integer('last_activity')->nullable()->index(); // <-- IMPORTANT
            $table->string('ip_address', 45)->nullable(); // <-- Optionnel
            $table->text('user_agent')->nullable(); // <-- Optionnel
            $table->timestamps();
        });


    }

    public function down(): void
    {

        Schema::dropIfExists('sessions');
    }
};
