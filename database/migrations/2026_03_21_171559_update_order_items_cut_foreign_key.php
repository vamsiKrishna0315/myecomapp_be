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
        Schema::table('order_items', function (Blueprint $table) {
            // Drop the old foreign key constraint
            $table->dropForeign(['cut_id']);

            // Add new foreign key constraint to cut_types table
            $table->foreign('cut_id')->references('id')->on('cut_types')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['cut_id']);

            // Add back the old foreign key constraint to product_cuts table
            $table->foreign('cut_id')->references('id')->on('product_cuts')->onDelete('restrict');
        });
    }
};
