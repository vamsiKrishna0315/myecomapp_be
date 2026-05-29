<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\OrderStatusTracking;
use Illuminate\Http\Request;
use Log;

final class OrderCancellationController extends ResponseController
{
    public function __invoke(string $orderUuid, Request $request)
    {
        $orderData = Orders::where('uuid', $orderUuid)
            ->with(['currentStatus', 'statusTrackings'])
            ->where('status', 1)
            ->get();

        Log::info('OrderCancellationController invoked', [
            'order_uuid' => $orderUuid,
            'order_found' => $orderData->toArray(),
        ]);
        $canCancel = true;
        $reason = null;
        if ($orderData->isNotEmpty()) {
            $currentStatus = $orderData->first()->currentStatus->code ?? null;
            if (in_array($currentStatus, ['delivered', 'returned'])) {
                $canCancel = false;
                $reason = 'Order already delivered';
            }
        } else {
            $canCancel = false;
            $reason = 'Order not found.';
        }

        Log::info('OrderCancellationController invoked', [
            'order_uuid' => $orderUuid,
            'order_found' => $orderData->isNotEmpty() ? $orderData->toArray() : null,
            'can_cancel' => $canCancel,
            'reason' => $reason,
        ]);

        if ($canCancel) {
            $getCancelledStatus = OrderStatuses::where('code', 'cancelled')->first();
            if (! $getCancelledStatus) {
                return $this->sendError('Cancelled status not found', 404);
            }
            $order = $orderData->first();
            $order->update([
                'current_status_id' => $getCancelledStatus->id,
                'current_status_code' => $getCancelledStatus->code,
                'is_cancelled' => 1,
                'cancelled_at' => now(),
            ]);

            $trackingData = [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'driver_id' => $order->driver_id ?? null,
                'store_vendor_id' => $order->storeVendorAssignment?->store_vendor_id ?? null,
                'order_status_id' => $getCancelledStatus->id,
                'status_code' => $getCancelledStatus->code,
                'status_name' => $getCancelledStatus->name,
                'description' => "Status updated to {$getCancelledStatus->name}",
                'lat' => $request->input('lat', null),
                'lng' => $request->input('lng', null),
                'status' => 1,
            ];
            $orderStatusTracking = OrderStatusTracking::create($trackingData);
            // call that action to insert cancellation record
            $cancellationData = [
                'reason_description' => $request->input('reason', 'Cancelled by customer'),
            ];
            (new \App\Actions\Order\OrderCancellationAction())->execute($order, $cancellationData);

            return $this->returnResponse(['message' => 'Order cancelled successfully.'], 'Order cancelled successfully.');
        }
    }
}
