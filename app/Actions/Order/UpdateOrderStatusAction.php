<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\OrderStatusTracking;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class UpdateOrderStatusAction
{
    /**
     * Update the order status synchronously.
     *
     * @return void
     *
     * @throws Exception
     */
    public function execute(Orders $order, string $nextStatusCode, array $additionalData = [])
    {
        try {
            DB::beginTransaction();
            Log::info('Starting synchronous order status update', [
                'order_uuid' => $order->uuid,
                'order_id' => $order->id,
                'next_status_code' => $nextStatusCode,
            ]);
            $nextStatus = OrderStatuses::where('code', $nextStatusCode)
                ->where('status', 1)
                ->first();

            if (! $nextStatus) {
                Log::error('Next status not found', [
                    'status_code' => $nextStatusCode,
                ]);
                throw new Exception('Next status not found');
            }

            $trackingData = [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'driver_id' => $additionalData['driver_id'] ?? $order->driver_id ?? null,
                'store_vendor_id' => $additionalData['store_vendor_id'] ?? $order->storeVendorAssignment?->store_vendor_id ?? null,
                'order_status_id' => $nextStatus->id,
                'status_code' => $nextStatus->code,
                'status_name' => $nextStatus->name,
                'description' => "Status updated to {$nextStatus->name}",
                'lat' => null,
                'lng' => null,
                'status' => 1,
            ];

            $orderStatusTracking = OrderStatusTracking::create($trackingData);

            $order->update([
                'current_status_id' => $nextStatus->id,
                'current_status_code' => $nextStatus->code,
            ]);

            $this->updateOrderTimestamps($order, $nextStatus->code);

            DB::commit();

            Log::info('Order status updated successfully', [
                'order_uuid' => $order->uuid,
                'order_id' => $order->id,
                'new_status' => $nextStatus->code,
                'tracking_id' => $orderStatusTracking->id,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update order status', [
                'order_uuid' => $order->uuid,
                'next_status_code' => $nextStatusCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Update order timestamps based on status code.
     */
    private function updateOrderTimestamps(Orders $order, string $statusCode): void
    {
        $updates = [];

        switch ($statusCode) {
            case 'confirmed':
                $updates['confirmed_at'] = now();
                break;
            case 'delivered':
                $updates['delivered_at'] = now();
                break;
            case 'cancelled':
                $updates['cancelled_at'] = now();
                $updates['is_cancelled'] = true;
                break;
            case 'failed':
                $updates['failed_at'] = now();
                break;
            case 'returned':
                $updates['returned_at'] = now();
                break;
        }

        if (! empty($updates)) {
            $order->update($updates);
        }
    }
}
