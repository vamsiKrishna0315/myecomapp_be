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
        Schema::create('combo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained('combos')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('cut_type_id')->constrained('cut_types')->onDelete('cascade');
            $table->decimal('weight', 8, 2);
            $table->string('weight_unit', 20)->default('kg');
            $table->integer('quantity')->default(1);
            $table->decimal('item_price', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['combo_id', 'product_id', 'cut_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_items');
    }
};
