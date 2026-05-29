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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
           // $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade'); need to add 
           // after customers table is created
            $table->text('description');
            $table->tinyInteger('rating')->unsigned()->comment('Rating from 1 to 5');
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');
            $table->tinyInteger('show_live')->default(0)->comment('0-> No, 1-> Yes');
            $table->timestamps();
        });
    }
};
