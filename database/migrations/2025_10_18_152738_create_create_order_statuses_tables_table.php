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
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // pending, confirmed, etc.
            $table->string('name');
            $table->string('color')->default('#gray'); // For UI display
            $table->string('description')->nullable();
            $table->integer('sequence')->default(0); // Order flow sequence
            $table->boolean('is_final')->default(false); // delivered, cancelled, returned
            $table->boolean('is_cancellable')->default(true);
            $table->integer('display_order')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');
            $table->timestamps();
            
            $table->index('code');
            $table->index('sequence');
        });

        // Insert default order statuses
        DB::table('order_statuses')->insert([
            ['code' => 'pending', 'name' => 'Order Placed', 'color' => '#FFA500', 'sequence' => 1, 'is_final' => false, 'is_cancellable' => true, 'display_order' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'confirmed', 'name' => 'Order Confirmed', 'color' => '#4CAF50', 'sequence' => 2, 'is_final' => false, 'is_cancellable' => true, 'display_order' => 2, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'processing', 'name' => 'Processing', 'color' => '#2196F3', 'sequence' => 3, 'is_final' => false, 'is_cancellable' => true, 'display_order' => 3, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ready_for_pickup', 'name' => 'Ready for Pickup', 'color' => '#9C27B0', 'sequence' => 4, 'is_final' => false, 'is_cancellable' => false, 'display_order' => 4, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'assigned_to_driver', 'name' => 'Assigned to Driver', 'color' => '#FF9800', 'sequence' => 5, 'is_final' => false, 'is_cancellable' => false, 'display_order' => 5, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'driver_accepted', 'name' => 'Driver Accepted', 'color' => '#00BCD4', 'sequence' => 6, 'is_final' => false, 'is_cancellable' => false, 'display_order' => 6, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'driver_at_store', 'name' => 'Driver at Store', 'color' => '#3F51B5', 'sequence' => 7, 'is_final' => false, 'is_cancellable' => false, 'display_order' => 7, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'driver_picked_up', 'name' => 'Order Picked Up', 'color' => '#673AB7', 'sequence' => 8, 'is_final' => false, 'is_cancellable' => false, 'display_order' => 8, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'driver_nearby', 'name' => 'Driver Nearby', 'color' => '#E91E63', 'sequence' => 9, 'is_final' => false, 'is_cancellable' => false, 'display_order' => 9, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'driver_reached', 'name' => 'Driver Reached', 'color' => '#FF5722', 'sequence' => 10, 'is_final' => false, 'is_cancellable' => false, 'display_order' => 10, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'delivered', 'name' => 'Delivered', 'color' => '#4CAF50', 'sequence' => 11, 'is_final' => true, 'is_cancellable' => false, 'display_order' => 11, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'cancelled', 'name' => 'Cancelled', 'color' => '#F44336', 'sequence' => 99, 'is_final' => true, 'is_cancellable' => false, 'display_order' => 12, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'returned', 'name' => 'Returned', 'color' => '#795548', 'sequence' => 98, 'is_final' => true, 'is_cancellable' => false, 'display_order' => 13, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'failed', 'name' => 'Failed', 'color' => '#9E9E9E', 'sequence' => 97, 'is_final' => true, 'is_cancellable' => false, 'display_order' => 14, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
