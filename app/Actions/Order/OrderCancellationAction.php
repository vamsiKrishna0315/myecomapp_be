<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Models\OrderCancellation;
use App\Models\Orders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class OrderCancellationAction
{
    /**
     * Handle the order cancellation logic and insert a record.
     */
    public function execute(Orders $order, array $data): OrderCancellation
    {
        return DB::transaction(function () use ($order, $data) {
            $cancellation = OrderCancellation::create([
                'order_id' => $order->id,
                'cancelled_by' => $data['cancelled_by'] ?? 'customer',
                'cancelled_by_id' => $data['cancelled_by_id'] ?? auth('customer-api')->id(),
                'reason_code' => $data['reason_code'] ?? 'customer_request',
                'reason_description' => $data['reason_description'],
                'refund_amount' => $data['refund_amount'] ?? 0,
                'refund_status' => $data['refund_status'] ?? 0,
                'cancelled_at' => $data['cancelled_at'] ?? now(),
                'refund_processed_at' => $data['refund_processed_at'] ?? null,
                'processed_by' => $data['processed_by'] ?? null,
                'admin_notes' => $data['admin_notes'] ?? null,
            ]);

            Log::info('Order cancelled and record inserted', [
                'order_id' => $order->id,
                'cancellation_id' => $cancellation->id,
            ]);

            return $cancellation;
        });
    }
}
