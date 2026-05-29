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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->string('category_type');
            $table->tinyInteger('status')
                  ->default(1)
                  ->comment('0-> Inactive, 1-> Active');
            $table->timestamps();
        });
    }
};
