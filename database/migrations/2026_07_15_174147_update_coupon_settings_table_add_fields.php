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
        Schema::table('coupon_settings', function (Blueprint $table) {
            $table->renameColumn('referral_discount_percentage', 'referral_discount_value');
        });

        Schema::table('coupon_settings', function (Blueprint $table) {
            $table->tinyInteger('referral_discount_type')->default(0)->after('id')->comment('0-> Percentage, 1-> Fixed Amount');
            $table->decimal('referral_min_order_amount', 10, 2)->default(0)->after('referral_discount_value');
            $table->decimal('referral_max_discount_amount', 10, 2)->nullable()->after('referral_min_order_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupon_settings', function (Blueprint $table) {
            $table->dropColumn(['referral_discount_type', 'referral_min_order_amount', 'referral_max_discount_amount']);
        });

        Schema::table('coupon_settings', function (Blueprint $table) {
            $table->renameColumn('referral_discount_value', 'referral_discount_percentage');
        });
    }
};
