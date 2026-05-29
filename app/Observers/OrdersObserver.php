<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\OrderCreated;
use App\Models\Orders;
use App\Models\StoreVendorOrders;
use Exception;
use Log;

final class OrdersObserver
{
    /**
     * Handle the Orders "created" event.
     */
    public function created(Orders $order)
    {
        Log::info('OrdersObserver: created event for order', ['order_id' => $order->id]);
        // Fallback: if a pending store vendor assignment exists in session, insert it here.
        try {
            $pending = session('pending_store_vendor_assignment');
            if (empty($pending)) {
                Log::info('No pending_store_vendor_assignment found in session for order', ['order_id' => $order->id]);
                $this->addStoreVendorToOrder($order);
            }
            if ($pending && isset($pending['store_id']) && isset($pending['store_vendor_id'])) {
                $exists = StoreVendorOrders::where('order_id', $order->id)->exists();
                if (! $exists) {
                    StoreVendorOrders::create([
                        'order_id' => $order->id,
                        'customer_id' => $order->customer_id,
                        'store_id' => $pending['store_id'],
                        'store_vendor_id' => $pending['store_vendor_id'],
                        'is_eligible' => $pending['is_eligible'] ?? true,
                        'status' => 1,
                    ]);
                    Log::info('StoreVendorOrders created by OrdersObserver (fallback)', [
                        'order_id' => $order->id,
                        'store_id' => $pending['store_id'],
                        'store_vendor_id' => $pending['store_vendor_id'],
                    ]);

                    // Clear session
                    session()->forget('pending_store_vendor_assignment');
                } else {
                    Log::info('StoreVendorOrders already exists, skipping observer insertion', ['order_id' => $order->id]);
                }
            }
        } catch (Exception $e) {
            Log::error('OrdersObserver fallback insertion failed: '.$e->getMessage(), ['order_id' => $order->id]);
        }

        event(new OrderCreated($order));
    }

    private function addStoreVendorToOrder($orders)
    {
        $result = getStoreVendorForOrder($orders);
    }
}
