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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_cut_id')->nullable()->constrained('product_cuts')->onDelete('cascade');

            // Quantity and weight details
            $table->decimal('quantity', 10, 3)->default(1); // Can be pieces or weight
            $table->string('quantity_unit', 20)->default('kg'); // kg, piece, etc.
            $table->decimal('weight', 10, 3)->nullable(); // Actual weight for weight-based items

            // Price details (captured at time of adding to cart)
            $table->decimal('unit_price', 10, 2); // Price per unit (kg/piece)
            $table->decimal('total_price', 10, 2); // Calculated total price

            // Special instructions or notes
            $table->text('special_instructions')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');
            // Session tracking for guest users (optional)
            $table->string('session_id', 255)->nullable();

            $table->timestamps();

            // Indexes for better performance
            $table->index('customer_id');
            $table->index('product_id');
            $table->index('product_cut_id');
            $table->index('session_id');

            // Composite index for faster cart retrieval
            $table->index(['customer_id', 'product_id', 'product_cut_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
