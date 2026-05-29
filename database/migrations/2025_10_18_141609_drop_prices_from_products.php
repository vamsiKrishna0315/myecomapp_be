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
            $table->dropColumn([
                'price',
                'cost',
                'compare_at_price',
                'currency',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->after('long_description');
            $table->decimal('cost', 10, 2)->nullable()->after('price');
            $table->decimal('compare_at_price', 10, 2)->nullable()->after('cost');
            $table->string('currency', 3)->default('INR')->after('compare_at_price');
        });
    }
};
