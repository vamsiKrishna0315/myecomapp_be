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
        Schema::table('banners', function (Blueprint $table) {
            $table->tinyInteger('show_live')
                ->default(0)
                ->comment('0-> No, 1-> Yes')
                ->after('redirect_link');
        });
    }
};
