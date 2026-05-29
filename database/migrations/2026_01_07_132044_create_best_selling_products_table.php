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
        Schema::create('best_selling_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('cut_type_id')->nullable()->constrained('cut_types')->onDelete('cascade');
            $table->integer('total_orders')->default(0);
            $table->integer('total_quantity_sold')->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->decimal('price', 10, 2)->nullable(); // Snapshot of product price
            $table->integer('rank')->default(0);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->tinyInteger('status')->default(1); // 1=active, 0=inactive
            $table->timestamps();

            // Indexes for performance
            $table->index('rank');
            $table->index(['category_id', 'rank']);
            $table->index(['status', 'rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('best_selling_products');
    }
};
