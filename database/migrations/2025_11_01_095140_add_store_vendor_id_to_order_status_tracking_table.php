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
        Schema::table('order_status_tracking', function (Blueprint $table) {
            $table->unsignedBigInteger('store_vendor_id')->nullable()->after('driver_id');
            
            // Add foreign key constraint
            $table->foreign('store_vendor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
                
            // Add index for faster queries
            $table->index('store_vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_status_tracking', function (Blueprint $table) {
            $table->dropForeign(['store_vendor_id']);
            $table->dropIndex(['store_vendor_id']);
            $table->dropColumn('store_vendor_id');
        });
    }
};
