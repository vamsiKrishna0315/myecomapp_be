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
        Schema::table('users', function (Blueprint $table) {
            $table->string('location')->nullable()->default(null)->after('store_id');
            $table->decimal('store_lat', 10, 7)->nullable()->default(null)->after('location');
            $table->decimal('store_lng', 10, 7)->nullable()->default(null)->after('store_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['location', 'store_lat', 'store_lng']);
        });
    }
};
