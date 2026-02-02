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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_id')->constrained();
            $table->string('title');
            $table->text('description');
            $table->json('instructions');
            $table->dateTime('deadline');
            $table->float('max_points');
            $table->json('allowed_formats'); // [pdf, doc, zip]
            $table->integer('max_file_size'); // en MB
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
