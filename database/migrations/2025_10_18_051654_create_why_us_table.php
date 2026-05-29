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
        Schema::create('why_us', function (Blueprint $table) {
            $table->id();
            $table->text('event_title')->nullable();
            $table->string('description')->nullable();
            $table->year('year')->nullable();
            $table->string('image')->nullable();
            $table->tinyInteger('show_live')->default(0)->comment('0-> No, 1-> Yes');
            $table->tinyInteger('status')->default(1)->comment('0-> Inactive, 1-> active');
            $table->timestamps();
        });
    }
};
