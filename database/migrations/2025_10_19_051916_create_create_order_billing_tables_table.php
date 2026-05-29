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
       Schema::create('order_billing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('billing_type_id')->constrained('billing_types')->onDelete('restrict');
            
            $table->decimal('amount', 10, 2);
            $table->string('transaction_id')->nullable()->unique();
            $table->string('payment_gateway')->nullable(); // razorpay, phonepe, paytm
            $table->json('payment_response')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');


            $table->tinyInteger('billing_status')->default(0)->comment('0: Pending, 1: Paid, 2: Failed, 3: Refunded');

            $table->timestamp('paid_at')->nullable();
            
            // Refund details
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_transaction_id')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_reason')->nullable();
            
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('transaction_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('create_order_billing_tables');
    }
};
