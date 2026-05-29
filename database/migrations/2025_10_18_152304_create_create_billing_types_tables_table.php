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
        Schema::create('billing_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');
            $table->timestamps();
            
            $table->index('slug');
            $table->index('status');
        });

        // Insert default billing types
        DB::table('billing_types')->insert([
            ['slug' => 'cash', 'name' => 'Cash on Delivery', 'display_order' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'cod', 'name' => 'COD', 'display_order' => 2, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'upi', 'name' => 'UPI Payment', 'display_order' => 3, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'credit_card', 'name' => 'Credit Card', 'display_order' => 4, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'debit_card', 'name' => 'Debit Card', 'display_order' => 5, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
