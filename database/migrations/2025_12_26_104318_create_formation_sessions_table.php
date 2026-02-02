<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('max_participants');
            $table->integer('current_participants')->default(0);
            $table->enum('status', ['planifie', 'en_cours', 'termine'])->default('planifie');
            $table->foreignId('formateur_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('formation_session_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_session_id')->constrained('formation_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
            $table->enum('status', ['active', 'completed', 'dropped'])->default('active');
            $table->timestamps();
            $table->unique(['formation_session_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formation_session_participants');
        Schema::dropIfExists('formation_sessions');
    }
};
