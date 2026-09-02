<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE club_presidency_transfers MODIFY COLUMN reason ENUM('ban', 'account_deletion', 'vacant') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE club_presidency_transfers MODIFY COLUMN reason ENUM('ban', 'account_deletion') NOT NULL");
    }
};