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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 15)->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->tinyInteger('vehicle_type'); // bike, car, van  write a enum later
            $table->string('vehicle_number');
            $table->string('license_number');
            $table->date('license_expiry_date')->nullable();
            $table->string('insurance_number')->nullable();
            $table->date('insurance_expiry_date')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->default('India');
            $table->string('profile_image')->nullable();
            $table->string('driver_license_image')->nullable();
            $table->string('vehicle_registration_image')->nullable();
            $table->string('insurance_image')->nullable();
            $table->tinyInteger('experience_years')->default(0);
            $table->tinyInteger('is_verified')->default(false);
            $table->tinyInteger('verified_by')->default(0);
            $table->decimal('current_lat', 10, 7)->nullable();
            $table->decimal('current_lng', 10, 7)->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_deliveries')->default(0);
            $table->boolean('is_available')->default(true);
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');
            $table->timestamps();
            
            $table->index(['is_available', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('create_drivers_tables');
    }
};
