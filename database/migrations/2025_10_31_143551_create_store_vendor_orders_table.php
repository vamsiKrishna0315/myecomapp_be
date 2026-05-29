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
        Schema::create('store_vendor_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('restrict');
            $table->foreignId('store_vendor_id')->constrained('users')->onDelete('restrict');
            $table->boolean('is_eligible')->default(true)->index();
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');
            $table->timestamps();
            
            // Indexes
            $table->index('order_id');
            $table->index('customer_id');
            $table->index('store_id');
            $table->index('store_vendor_id');
            $table->index(['store_vendor_id', 'is_eligible']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_vendor_orders');
    }
};
