<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Add new cuttype_id
            $table->unsignedBigInteger('cuttype_id')->nullable()->after('product_cut_id');
            $table->foreign('cuttype_id')->references('id')->on('cut_types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['cuttype_id']);
            $table->dropColumn('cuttype_id');
        });
    }
};
