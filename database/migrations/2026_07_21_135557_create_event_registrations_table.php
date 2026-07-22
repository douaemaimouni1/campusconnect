<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamp('registered_at')->useCurrent();

            $table->enum('status', [
                'pending',
                'confirmed',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->unique(['user_id', 'event_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};