<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_trip_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->string('source_status_code')->nullable()->index();
            $table->string('trip_phase', 32)->nullable()->index();
            $table->timestamp('recorded_at')->index();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['order_id', 'recorded_at'], 'driver_trip_order_recorded_idx');
            $table->index(['driver_id', 'recorded_at'], 'driver_trip_driver_recorded_idx');
            $table->index(['order_id', 'driver_id'], 'driver_trip_order_driver_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_trip_locations');
    }
};
