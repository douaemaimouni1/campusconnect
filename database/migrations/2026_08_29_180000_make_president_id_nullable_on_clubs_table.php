<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Rend president_id nullable sur clubs, et remplace la contrainte
     * restrictOnDelete() par nullOnDelete() : un club peut désormais
     * exister sans président (en attente d'intervention administrative,
     * voir décision "Gestion du départ ou du bannissement d'un président
     * de club").
     */
    public function up(): void
    {
        // Étape 1 : on supprime l'ancienne contrainte de clé étrangère
        // (celle qui empêchait de supprimer un utilisateur tant qu'il
        // était président d'un club).
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropForeign(['president_id']);
        });

        // Étape 2 : on rend la colonne nullable.
        Schema::table('clubs', function (Blueprint $table) {
            $table->foreignId('president_id')->nullable()->change();
        });

        // Étape 3 : on recrée la contrainte avec le nouveau comportement.
        // nullOnDelete() : si un utilisateur est supprimé alors qu'il est
        // encore president_id d'un club (filet de sécurité, ne devrait plus
        // arriver grâce à la logique applicative), MySQL met automatiquement
        // president_id à NULL au lieu de bloquer la suppression.
        Schema::table('clubs', function (Blueprint $table) {
            $table->foreign('president_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Remet le comportement d'origine (colonne obligatoire + restrictOnDelete).
     * Attention : si des clubs ont president_id = NULL au moment du rollback,
     * cette étape échouera (normal, une colonne NOT NULL ne peut pas contenir
     * de valeurs NULL existantes) — il faudrait alors assigner manuellement
     * un président à ces clubs avant de pouvoir revenir en arrière.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropForeign(['president_id']);
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->foreignId('president_id')->nullable(false)->change();
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->foreign('president_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }
};