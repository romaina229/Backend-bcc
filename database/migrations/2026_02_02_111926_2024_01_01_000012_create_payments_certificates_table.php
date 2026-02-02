<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cours_id')->constrained('courses')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->string('devise', 3)->default('XOF');
            $table->string('methode_paiement')->nullable();
            $table->enum('statut', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_id')->unique()->nullable();
            $table->string('reference')->unique()->nullable();
            $table->timestamp('date_paiement')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('numero_certificat')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cours_id')->constrained('courses')->onDelete('cascade');
            $table->string('nom_complet');
            $table->string('titre_cours');
            $table->date('date_obtention');
            $table->float('note_finale')->nullable();
            $table->integer('duree_cours')->nullable();
            $table->date('date_emission');
            $table->date('date_expiration')->nullable();
            $table->enum('statut', ['valide', 'expire', 'revoque'])->default('valide');
            $table->text('qr_code')->nullable();
            $table->string('url_verification')->unique();
            $table->string('signataire')->nullable();
            $table->string('fonction_signataire')->nullable();
            $table->string('mention')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('payments');
    }
};