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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('banner_name');
            $table->string('banner_path');
            $table->string('redirect_link')->nullable();
            $table->tinyInteger('status')
                  ->default(1)
                  ->comment('0->inactive, 1->Active');
            $table->timestamps();
        });
    }
};
