<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\StoreVendorOrders;

final class CreateStoreVendorOrderRecord
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        $order = $event->order;
        // You may need to adjust how you get store_vendor_id, store_id, customer_id, etc.
        // Check if a StoreVendorOrders record already exists for this order
        $exists = StoreVendorOrders::where('order_id', $order->id)->exists();
        if (! $exists) {
            StoreVendorOrders::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id ?? null,
                'store_id' => 1, // Hardcoded as requested
                'store_vendor_id' => 1, // Hardcoded as requested
                'is_eligible' => true,
                'status' => 1,
            ]);
        }
    }
}
