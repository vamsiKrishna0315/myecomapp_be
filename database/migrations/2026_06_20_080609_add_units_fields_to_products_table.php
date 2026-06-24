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
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'allowed_units')) {
                $table->json('allowed_units')->nullable()->after('weight_unit');
            }
            if (! Schema::hasColumn('products', 'grams_per_piece')) {
                $table->decimal('grams_per_piece', 10, 3)->nullable()->after('allowed_units');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'allowed_units')) {
                $table->dropColumn('allowed_units');
            }
            if (Schema::hasColumn('products', 'grams_per_piece')) {
                $table->dropColumn('grams_per_piece');
            }
        });
    }
};
