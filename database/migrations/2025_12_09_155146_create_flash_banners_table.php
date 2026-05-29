<?php

declare(strict_types=1);

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
        Schema::create('flash_banners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('redirect_link')->nullable();
            $table->string('image');
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_live')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flash_banners');
    }
};
