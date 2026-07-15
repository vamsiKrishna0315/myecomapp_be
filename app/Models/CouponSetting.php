<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Model;

final class CouponSetting extends Model
{
    protected $fillable = [
        'referral_discount_type',
        'referral_discount_value',
        'referral_min_order_amount',
        'referral_max_discount_amount',
    ];

    protected $casts = [
        'referral_discount_type' => 'integer',
        'referral_discount_value' => 'decimal:2',
        'referral_min_order_amount' => 'decimal:2',
        'referral_max_discount_amount' => 'decimal:2',
    ];

    /**
     * Get the single, global referral coupon configuration for the app.
     */
    public static function current(): self
    {
        return self::query()->first() ?? new self([
            'referral_discount_type' => CouponType::Percentage->value,
            'referral_discount_value' => 10,
            'referral_min_order_amount' => 0,
            'referral_max_discount_amount' => null,
        ]);
    }
}
