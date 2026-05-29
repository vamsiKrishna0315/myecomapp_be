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
        Schema::table('best_selling_products', function (Blueprint $table) {
            $table->decimal('frequently_ordered_weight', 8, 2)->nullable()->after('price');
            $table->string('weight_unit', 10)->default('kg')->after('frequently_ordered_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('best_selling_products', function (Blueprint $table) {
            $table->dropColumn(['frequently_ordered_weight', 'weight_unit']);
        });
    }
};
