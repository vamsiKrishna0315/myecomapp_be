<?php

declare(strict_types=1);

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
        Schema::table('drivers', function (Blueprint $table) {
            $table->timestamp('location_updated_at')->nullable()->after('current_lng');
            $table->index(['status', 'is_available', 'location_updated_at'], 'drivers_dispatch_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropIndex('drivers_dispatch_lookup_idx');
            $table->dropColumn('location_updated_at');
        });
    }
};
