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
            // Pricing fields
            $table->decimal('price_per_kg', 10, 2)->nullable()->after('price');
            $table->decimal('price_per_piece', 10, 2)->nullable()->after('price_per_kg');

            // Weight fields
            $table->decimal('minimum_weight', 8, 2)->default(0.5)->after('weight');
            $table->string('weight_unit', 20)->default('kg')->after('minimum_weight');
            $table->decimal('net_weight', 8, 2)->nullable()->after('weight_unit');

            // Cut/Preparation fields
            $table->string('preparation_style')->nullable()->after('description');
            $table->boolean('is_cleaned')->default(true)->after('preparation_style');
            $table->boolean('is_skinless')->default(false)->after('is_cleaned');

            // Stock fields
            $table->string('stock_unit', 20)->default('kg')->after('stock_quantity');

            // Order requirements
            $table->boolean('requires_advance_order')->default(false)->after('track_inventory');
            $table->integer('preparation_time')->nullable()->comment('in minutes')->after('requires_advance_order');

            // Display fields
            $table->boolean('popular')->default(false)->after('is_best_seller');
            $table->integer('display_order')->default(0)->after('popular');

            // Nutrition fields
            $table->decimal('protein_per_100g', 5, 2)->nullable()->after('dimensions');
            $table->decimal('fat_per_100g', 5, 2)->nullable()->after('protein_per_100g');
            $table->integer('calories_per_100g')->nullable()->after('fat_per_100g');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'price_per_kg',
                'price_per_piece',
                'minimum_weight',
                'weight_unit',
                'net_weight',
                'preparation_style',
                'is_cleaned',
                'is_skinless',
                'stock_unit',
                'requires_advance_order',
                'preparation_time',
                'popular',
                'display_order',
                'protein_per_100g',
                'fat_per_100g',
                'calories_per_100g',
            ]);
        });
    }
};
