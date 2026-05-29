<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\OrderStatusTracking;

class CreateOrderStatusTracking
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event)
    {
        $order = $event->order;
        \Log::info('CreateOrderStatusTracking listener triggered', ['order_id' => $order->id, 'driver_id' => $order->driver_id]);
        if ($order->driver_id) {
            OrderStatusTracking::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'driver_id' => $order->driver_id,
                'order_status_id' => $order->current_status_id,
                'status_code' => $order->current_status_code,
                'status_name' => optional($order->currentStatus)->name,
                'status' => 1,
            ]);
            \Log::info('OrderStatusTracking record created for order', ['order_id' => $order->id]);
        }
    }
}
