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
        Schema::create('gamification_badge_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Badge name');
            $table->text('description')->nullable()->comment('Badge description');
            $table->string('icon')->nullable()->comment('Icon identifier');
            $table->integer('level')->default(1)->comment('Badge difficulty level');
            $table->string('trigger_type')->comment('order_count, reputation_points, etc.');
            $table->integer('threshold_value')->comment('Value to achieve for badge');
            $table->boolean('is_active')->default(true)->comment('Whether this badge is active');
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gamification_badge_rules');
    }
};
