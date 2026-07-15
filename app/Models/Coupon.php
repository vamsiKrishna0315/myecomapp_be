<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Coupon extends Model
{
    protected $fillable = [
        'customer_id',
        'is_referral',
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_per_customer',
        'used_count',
        'valid_from',
        'valid_until',
        'description',
        'status',
    ];

    protected $casts = [
        'is_referral' => 'boolean',
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_per_customer' => 'integer',
        'used_count' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'status' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Orders placed using this coupon's code (matched by code, not a foreign key).
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Orders::class, 'coupon_code', 'code');
    }
}
