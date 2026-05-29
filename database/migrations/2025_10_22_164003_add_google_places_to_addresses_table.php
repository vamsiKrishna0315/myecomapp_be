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
        Schema::table('addresses', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('country')->comment('Latitude from Google Places');
            $table->decimal('lng', 10, 7)->nullable()->after('lat')->comment('Longitude from Google Places');
            $table->json('google_places_data')->nullable()->after('lng')->comment('Complete Google Places API response data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'google_places_data']);
        });
    }
};
