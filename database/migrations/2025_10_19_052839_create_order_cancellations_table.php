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
       Schema::create('order_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->enum('cancelled_by', ['customer', 'admin', 'driver', 'system']);
            $table->unsignedBigInteger('cancelled_by_id')->nullable(); // ID of user/admin/driver
            
            $table->string('reason_code'); // out_of_stock, customer_request, etc.
            $table->text('reason_description')->nullable();
            
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->tinyInteger('refund_status')->default(0)->comment('0-> Not Applicable, 1-> Pending, 2-> Processing, 3-> Completed, 4-> Failed');
            $table->timestamp('cancelled_at');
            $table->timestamp('refund_processed_at')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable(); // Admin who processed
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            
            $table->unique('order_id'); // One cancellation record per order
            $table->index('refund_status');
            $table->index('cancelled_by');
        });
    }
};
