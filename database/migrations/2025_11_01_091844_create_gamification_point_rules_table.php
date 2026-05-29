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
        Schema::create('gamification_point_rules', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->unique()->comment('order_created, order_completed, order_delivered, high_value_order, daily_streak');
            $table->string('name')->comment('Display name for the point rule');
            $table->integer('points')->default(0)->comment('Points to award');
            $table->boolean('is_active')->default(true)->comment('Whether this rule is active');
            $table->json('conditions')->nullable()->comment('Additional conditions like min_order_value');
            $table->text('description')->nullable()->comment('Description of when points are awarded');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gamification_point_rules');
    }
};
