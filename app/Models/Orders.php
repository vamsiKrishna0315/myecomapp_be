<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class Orders extends Model
{
    protected $fillable = [
        'uuid',
        'order_number',
        'razorpay_order_id',
        'razorpay_payment_id',
        'customer_id',
        'delivery_address_id',
        'billing_address_id',
        'delivery_date',
        'delivery_time_slot',
        'special_instructions',
        'subtotal',
        'coupon_code',
        'discount_amount',
        'discount_type',
        'tax_percentage',
        'tax_amount',
        'delivery_charge',
        'total_amount',
        'status',
        'billing_type_id',
        'payment_status',
        'current_status_id',
        'current_status_code',
        'driver_id',
        'is_cancelled',
        'cancelled_at',
        'confirmed_at',
        'delivered_at',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_type' => 'integer',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'status' => 'integer',
        'billing_type_id' => 'integer',
        'payment_status' => 'integer',
        'current_status_id' => 'integer',
        'driver_id' => 'integer',
        'is_cancelled' => 'boolean',
        'cancelled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function billingType()
    {
        return $this->belongsTo(BillingType::class, 'billing_type_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function currentStatus()
    {
        return $this->belongsTo(OrderStatuses::class, 'current_status_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    public function billing()
    {
        return $this->hasOne(OrderBilling::class, 'order_id');
    }

    public function cancellation()
    {
        return $this->hasOne(OrderCancellation::class, 'order_id');
    }

    public function review()
    {
        return $this->hasOne(OrderReview::class, 'order_id');
    }

    public function statusTracking()
    {
        return $this->hasMany(OrderStatusTracking::class, 'order_id');
    }

    public function latestStatusTracking()
    {
        return $this->hasOne(OrderStatusTracking::class, 'order_id')->latest();
    }

    /**
     * Get the store vendor assignment for this order.
     */
    public function storeVendorAssignment()
    {
        return $this->hasOne(StoreVendorOrders::class, 'order_id');
    }

    /**
     * Get the Cart associated with the Customer.
     */
    public function cart()
    {
        return $this->hasOne(CartItem::class, 'customer_id', 'customer_id');
    }

    public function statusTrackings()
    {
        return $this->hasMany(OrderStatusTracking::class, 'order_id', 'id')->orderByDesc('id');
    }

    public function tripLocations()
    {
        return $this->hasMany(DriverTripLocation::class, 'order_id');
    }

    protected static function booted()
    {
        self::creating(function ($order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
            if (empty($order->order_number)) {
                $year = date('Y');
                $prefix = 'ORD-'.$year.'-';
                $latestOrder = self::where('order_number', 'like', $prefix.'%')
                    ->orderByDesc('id')
                    ->first();
                $nextNumber = 1;
                if ($latestOrder && preg_match('/ORD-'.$year.'-(\d+)/', $latestOrder->order_number, $matches)) {
                    $nextNumber = (int) ($matches[1]) + 1;
                }
                $numLength = max(4, mb_strlen((string) $nextNumber));
                $order->order_number = $prefix.mb_str_pad((string) $nextNumber, $numLength, '0', STR_PAD_LEFT);
            }
        });
        self::observe(\App\Observers\OrdersObserver::class);
    }
}
