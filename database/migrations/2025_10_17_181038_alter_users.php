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
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_mobile_no', 20)->nullable()->unique()->after('email');
            $table->string('user_role', 50)->default('user')->after('user_mobile_no');
            $table->string('address', 255)->nullable()->after('password');
            $table->string('address_proof', 100)->nullable()->after('address');
            $table->string('finger_print', 255)->nullable()->after('address_proof');
            $table->date('joining_date')->nullable()->after('finger_print');
            $table->string('alternate_number', 20)->nullable()->after('joining_date');
            $table->date('dob')->nullable()->after('alternate_number');
            $table->decimal('salary', 10, 2)->nullable()->after('dob');
            $table->unsignedBigInteger('store_id')->nullable()->after('salary');
            $table->tinyInteger('status')
                  ->default(1)
                  ->comment('0->inactive, 1->Active')
                  ->after('store_id');
            
            // Add foreign key constraint for store_id
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
        });
    }
};
