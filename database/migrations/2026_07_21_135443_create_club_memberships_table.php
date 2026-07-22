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
            Schema::create('club_memberships', function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('club_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->enum('status', [
                    'pending',
                     'accepted',
                    'rejected',
                     'cancelled'
                    ])->default('pending');

                $table->timestamp('requested_at')->useCurrent();

                $table->dateTime('responded_at')->nullable();
                $table->unique(['user_id', 'club_id']);
                $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_memberships');
    }
};
