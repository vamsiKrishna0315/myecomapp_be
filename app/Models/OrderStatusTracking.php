<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderStatusTracking extends Model
{
    protected $table = 'order_status_tracking';

    protected $fillable = [
        'order_id',
        'customer_id',
        'driver_id',
        'store_vendor_id',
        'order_status_id',
        'status_code',
        'status_name',
        'lat',
        'lng',
        'description',
        'status',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order that this tracking belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    /**
     * Get the customer that this tracking belongs to.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the driver that this tracking belongs to.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    /**
     * Get the store vendor associated with this tracking.
     */
    public function storeVendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_vendor_id')
            ->where('user_role', 'store_vendor');
    }

    /**
     * Get the order status that this tracking belongs to.
     */
    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatuses::class, 'order_status_id');
    }

    /**
     * Scope a query to only include active tracking records.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include inactive tracking records.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope a query to filter by order.
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope a query to filter by status code.
     */
    public function scopeByStatusCode($query, $statusCode)
    {
        return $query->where('status_code', $statusCode);
    }

    /**
     * Register model observers
     */
    protected static function booted(): void
    {
        self::observe(\App\Observers\OrderStatusTrackingObserver::class);
    }
}
