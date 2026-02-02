
<?php
// 2024_01_01_000003_create_modules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 200);
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->foreignId('cours_id')
                  ->constrained('courses')
                  ->onDelete('cascade');

            $table->integer('duree')->nullable(); // en heures
            $table->integer('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->json('objectifs')->nullable();
            $table->json('prerequis')->nullable();
            $table->string('icon')->nullable();
            $table->enum('statut', ['brouillon', 'actif', 'archive'])->default('brouillon');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('modules');
    }
}
