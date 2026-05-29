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
         // Cut Types Table
        Schema::create('cut_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');
            $table->tinyInteger('show_live')->default(0)->comment('0-> No, 1-> Yes');
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index('slug');
            $table->index('status');
        });

         // Product  Grades Table
        Schema::create('product_grades', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_multiplier', 5, 2)->default(1.00)->comment('Multiplier for base price');
            $table->string('badge_color', 20)->nullable()->comment('Color for UI badge: success, warning, danger');
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');
            $table->tinyInteger('show_live')->default(0)->comment('0-> No, 1-> Yes');
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index('slug');
            $table->index('status');
        });

        Schema::create('product_cuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('cut_name');
            $table->string('cut_code')->unique();
            
            $table->decimal('price_per_kg', 10, 2);
            $table->decimal('price_per_piece', 10, 2)->nullable();
            $table->decimal('minimum_weight', 8, 2)->default(0.5);
            $table->string('weight_unit', 20)->default('kg');
            $table->decimal('net_weight', 8, 2)->nullable();
            
            $table->foreignId('cut_type_id')->nullable()->constrained('cut_types')->onDelete('set null');
            $table->string('preparation_style')->nullable();
            $table->foreignId('product_grade_id')->nullable()->constrained('product_grades')->onDelete('set null');
            $table->boolean('is_cleaned')->default(true);
            $table->boolean('is_skinless')->default(false);
            
            $table->decimal('stock_quantity', 10, 2)->default(0);
            $table->string('stock_unit', 20)->default('kg');
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> Active');
            $table->tinyInteger('show_live')->default(0)->comment('0-> No, 1-> Yes');
            $table->boolean('requires_advance_order')->default(false);
            $table->integer('preparation_time')->nullable()->comment('in minutes');
            
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('popular')->default(false);
            $table->integer('display_order')->default(0);
            
            $table->decimal('protein_per_100g', 5, 2)->nullable();
            $table->decimal('fat_per_100g', 5, 2)->nullable();
            $table->integer('calories_per_100g')->nullable();
            
            $table->timestamps();
            
            $table->index('product_id');
            $table->index('cut_type_id');
            $table->index('product_grade_id');
            $table->index('cut_code');
            $table->index('status');
            $table->index('show_live');
            $table->index('popular');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_cuts');
        Schema::dropIfExists('product_grades');
        Schema::dropIfExists('cut_types');
    }
};