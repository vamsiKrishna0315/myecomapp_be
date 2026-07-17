<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Order\OrderCancellationAction;
use App\Actions\Order\UpdateOrderStatusAction;
use App\Models\Driver;
use App\Models\Orders;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DriverOrderCancellationService
{
    /**
     * @return array<string, mixed>|null
     */
    public function cancel(Driver $driver, string $orderId, string $reasonCode, ?string $notes): ?array
    {
        $order = $this->findDriverOrder($driver, $orderId);

        if (! $order) {
            return null;
        }

        if ($order->current_status_code !== 'assigned_to_driver') {
            throw ValidationException::withMessages([
                'status' => ['This order can no longer be cancelled.'],
            ]);
        }

        DB::transaction(function () use ($order, $driver, $reasonCode, $notes): void {
            (new UpdateOrderStatusAction())->execute($order, 'cancelled', [
                'driver_id' => $driver->id,
            ]);

            (new OrderCancellationAction())->execute($order, [
                'cancelled_by' => 'driver',
                'cancelled_by_id' => $driver->id,
                'reason_code' => $reasonCode,
                'reason_description' => $notes ?: $this->reasonLabel($reasonCode),
            ]);

            $order->update(['driver_id' => null]);
        });

        return [
            'id' => $order->order_number,
            'requires_customer_confirmation' => true,
        ];
    }

    private function findDriverOrder(Driver $driver, string $orderId): ?Orders
    {
        return Orders::query()
            ->where('order_number', $orderId)
            ->where('driver_id', $driver->id)
            ->where('status', 1)
            ->where('is_cancelled', false)
            ->first();
    }

    private function reasonLabel(string $reasonCode): string
    {
        return match ($reasonCode) {
            'store_closed' => 'Store is closed',
            'product_unavailable' => 'Product not available',
            'customer_request' => 'Customer requested cancellation',
            'vehicle_issue' => 'Vehicle breakdown',
            default => 'Other reason',
        };
    }
}
