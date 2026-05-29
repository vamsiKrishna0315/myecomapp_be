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
        Schema::create('order_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            
            // Ratings
            $table->tinyInteger('product_rating')->nullable(); // 1-5
            $table->tinyInteger('delivery_rating')->nullable(); // 1-5
            $table->tinyInteger('overall_rating'); // 1-5
            
            $table->text('review')->nullable();
            $table->json('images')->nullable();
            
            $table->boolean('is_verified_purchase')->default(true);
            $table->tinyInteger('status')->default(1)->comment('0-> Hidden, 1-> Visible');
            
            $table->timestamps();
            
            $table->unique('order_id'); // One review per order
            $table->index(['customer_id', 'status']);
            $table->index('overall_rating');
        });
    }
};
