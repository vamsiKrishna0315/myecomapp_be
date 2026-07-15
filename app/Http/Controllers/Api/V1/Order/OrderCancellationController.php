<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Api\V1\ResponseController;
use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\OrderStatusTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class OrderCancellationController extends ResponseController
{
    public function __invoke(string $orderUuid, Request $request)
    {
        $orderData = Orders::where('uuid', $orderUuid)
            ->with(['currentStatus', 'statusTrackings'])
            ->where('status', 1)
            ->get();

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

        if (! $canCancel) {
            return $this->sendError($reason, 404);
        }

        $getCancelledStatus = OrderStatuses::where('code', 'cancelled')->first();
        if (! $getCancelledStatus) {
            return $this->sendError('Cancelled status not found', 404);
        }

        $order = $orderData->first();

        DB::transaction(function () use ($order, $getCancelledStatus, $request) {
            $order->update([
                'current_status_id' => $getCancelledStatus->id,
                'current_status_code' => $getCancelledStatus->code,
                'is_cancelled' => 1,
                'cancelled_at' => now(),
                'payment_status' => $order->payment_status === PaymentStatus::Paid->value
                    ? $order->payment_status
                    : PaymentStatus::Failed->value,
            ]);

            OrderStatusTracking::create([
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
            ]);

            (new \App\Actions\Order\OrderCancellationAction())->execute($order, [
                'reason_description' => $request->input('reason', 'Cancelled by customer'),
            ]);
        });

        return $this->returnResponse(['message' => 'Order cancelled successfully.'], 'Order cancelled successfully.');
    }
}
