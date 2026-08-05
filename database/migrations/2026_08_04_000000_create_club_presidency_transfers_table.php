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
        Schema::create('club_presidency_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('club_id')
                ->constrained('clubs')
                ->cascadeOnDelete();

            $table->foreignId('current_president_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('proposed_president_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('status', ['pending', 'accepted', 'declined'])
                ->default('pending');

            $table->enum('initiated_by', ['admin', 'self']);

            $table->enum('reason', ['ban', 'account_deletion']);

            $table->timestamp('responded_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_presidency_transfers');
    }
};