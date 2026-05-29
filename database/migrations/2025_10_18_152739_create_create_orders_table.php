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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            
            // Addresses
            $table->foreignId('delivery_address_id')->constrained('addresses')->onDelete('restrict');
            $table->foreignId('billing_address_id')->nullable()->constrained('addresses')->onDelete('restrict');
            
            // Delivery Details
            $table->date('delivery_date');
            $table->string('delivery_time_slot'); // e.g., "10:00 AM - 12:00 PM"
            $table->text('special_instructions')->nullable();
            
            // Pricing
            $table->decimal('subtotal', 10, 2);
            $table->string('coupon_code')->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->tinyInteger('discount_type')->nullable()->comment('0: Percentage, 1: Fixed');
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);

            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');
            
            // Payment
            $table->foreignId('billing_type_id')->constrained('billing_types')->onDelete('restrict');
            $table->tinyInteger('payment_status')->default(0)->comment('0: Pending, 1: Paid, 2: Failed, 3: Refunded, 4: Partial Refund');

            // Current Status - Denormalized for quick access
            $table->foreignId('current_status_id')->constrained('order_statuses')->onDelete('restrict');
            $table->string('current_status_code')->index(); // Duplicate for faster queries
            
            // Driver Assignment
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            
            // Cancellation (quick reference)
            $table->boolean('is_cancelled')->default(false)->index();
            $table->timestamp('cancelled_at')->nullable();
            
            // Important Timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            $table->index('order_number');
            $table->index('customer_id');
            $table->index(['current_status_code', 'payment_status']);
            $table->index('delivery_date');
            $table->index('driver_id');
        });
    }

  

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
