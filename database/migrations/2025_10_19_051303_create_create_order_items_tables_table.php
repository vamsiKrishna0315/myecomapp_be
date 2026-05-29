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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('cut_id')->constrained('product_cuts')->onDelete('restrict');
            
            // Product details at time of order (snapshot)
            $table->string('product_name');
            $table->string('cut_name');
            $table->string('sku');
            
            // Pricing
            $table->decimal('price_per_kg', 10, 2);
            $table->decimal('price_per_piece', 10, 2)->nullable();
            
            // Weight
            $table->decimal('ordered_weight', 8, 2); // Weight ordered by customer
            $table->decimal('actual_weight', 8, 2)->nullable(); // Actual weight after weighing
            $table->string('weight_unit', 20)->default('kg');
            
            // Line item calculations
            $table->decimal('line_subtotal', 10, 2);
            $table->decimal('line_discount', 10, 2)->default(0);
            $table->decimal('line_tax', 10, 2)->default(0);
            $table->decimal('line_total', 10, 2);
            
            // Cut preferences
            $table->string('preparation_style')->nullable();
            $table->boolean('is_cleaned')->default(true);
            $table->boolean('is_skinless')->default(false);
            $table->text('special_instructions')->nullable();
            
            // Item Status (simple)
            $table->tinyInteger('order_item_status')->unsigned()->default(0)->comment('0-> Pending, 1-> Confirmed, 2-> Preparing, 3-> Ready, 4-> Packed, 5-> Dispatched, 6-> Delivered, 7-> Cancelled, 8-> Returned'); // Use enum mapping in application code for detailed statuses
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');

            $table->timestamps();
            $table->index('order_id');
            $table->index(['product_id', 'cut_id']);
            $table->index('order_item_status');
        });
    }
};
