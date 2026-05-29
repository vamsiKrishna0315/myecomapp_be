<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all empty string emails to NULL
        DB::table('customers')
            ->where('email', '')
            ->update(['email' => null]);

        // The email column should already be nullable and unique
        // This migration just ensures data consistency
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert
    }
};
