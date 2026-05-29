<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreVendorOrders extends Model
{
    protected $table = 'store_vendor_orders';

    protected $fillable = [
        'order_id',
        'customer_id',
        'store_id',
        'store_vendor_id',
        'is_eligible',
        'status',
    ];

    protected $casts = [
        'is_eligible' => 'boolean',
        'status' => 'integer',
    ];

    /**
     * Get the order that this store vendor order belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    /**
     * Get the customer that placed the order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the store that the order belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * Get the store vendor who created the order.
     */
    public function storeVendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_vendor_id')
            ->where('user_role', 'store_vendor');
    }
}
