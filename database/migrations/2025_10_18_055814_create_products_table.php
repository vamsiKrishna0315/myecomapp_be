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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();

            $table->text('description')->nullable();
            $table->longText('long_description')->nullable();
            $table->json('specifications')->nullable();

            $table->decimal('price', 10, 2);
            $table->decimal('cost', 10, 2)->nullable();
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('INR');

            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(10);
            $table->boolean('track_inventory')->default(true);

            // $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->json('tags')->nullable();

            $table->string('primary_image')->nullable();
            $table->json('images')->nullable();

            $table->enum('status', ['active', 'draft', 'archived'])->default('draft');
            $table->boolean('is_visible')->default(true);
            $table->timestamp('published_at')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->decimal('weight', 8, 2)->nullable();
            $table->json('dimensions')->nullable();

            $table->timestamps();

            $table->index('sku');
            $table->index('slug');
            $table->index('status');
            $table->index('is_visible');
        });
    }
};
