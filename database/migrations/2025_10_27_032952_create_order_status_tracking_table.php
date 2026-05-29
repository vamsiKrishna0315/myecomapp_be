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
        Schema::create('order_status_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->foreignId('order_status_id')->constrained('order_statuses')->onDelete('restrict');
            $table->string('status_code')->index(); // Denormalized for faster queries
            $table->string('status_name'); // Denormalized for faster queries
            $table->decimal('lat', 10, 8)->nullable(); // Latitude with precision for GPS coordinates
            $table->decimal('lng', 11, 8)->nullable(); // Longitude with precision for GPS coordinates
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('order_id');
            $table->index(['order_id', 'status_code']);
            $table->index('customer_id');
            $table->index('driver_id');
            $table->index('order_status_id');
            $table->index('created_at'); // For chronological tracking
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_tracking');
    }
};
