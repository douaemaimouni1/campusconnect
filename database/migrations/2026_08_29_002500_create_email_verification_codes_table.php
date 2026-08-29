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
        Schema::create('email_verification_codes', function (Blueprint $table) {
            $table->id();

            // Le compte concerné par cette vérification.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // L'adresse email à laquelle le code a été envoyé.
            // Lors d'un changement d'email dans /profile, c'est le NOUVEL
            // email, pas encore celui enregistré sur le compte.
            $table->string('email');

            // Le code à 6 chiffres, stocké tel quel (pas besoin de le hasher :
            // il expire vite et n'a pas la même sensibilité qu'un mot de passe).
            $table->string('code', 6);

            // Contexte de génération du code : création de compte ou
            // changement d'email depuis le profil.
            $table->enum('purpose', ['register', 'profile_change']);

            // Nombre de tentatives incorrectes depuis le dernier code généré.
            $table->unsignedTinyInteger('attempts')->default(0);

            // Le code n'est plus valable après cette date (10 minutes après génération).
            $table->timestamp('expires_at');

            // Si renseigné, l'utilisateur est temporairement bloqué (2 minutes)
            // après 3 tentatives incorrectes, et ne peut pas réessayer avant cette date.
            $table->timestamp('blocked_until')->nullable();

            // Date du dernier envoi, pour appliquer le délai anti-spam
            // de 60 secondes avant de pouvoir demander un renvoi.
            $table->timestamp('last_sent_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_verification_codes');
    }
};