<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Order\UpdateOrderStatusAction;
use App\Models\Driver;
use App\Models\Orders;
use App\Models\OrderStatuses;
use Illuminate\Validation\ValidationException;

final class DriverOrderStatusService
{
    /**
     * @return array<string, mixed>|null
     */
    public function getAvailableStatuses(Driver $driver, string $orderId): ?array
    {
        $order = $this->findDriverOrder($driver, $orderId);

        if (! $order) {
            return null;
        }

        $currentStatus = $order->currentStatus
            ?? OrderStatuses::query()->whereKey($order->current_status_id)->where('status', 1)->first();

        if (! $currentStatus) {
            throw ValidationException::withMessages([
                'status' => ['Current order status is invalid.'],
            ]);
        }

        $statuses = OrderStatuses::query()
            ->select('id', 'code', 'name', 'color', 'description', 'sequence', 'is_final', 'display_order')
            ->where('status', 1)
            ->whereNotIn('code', OrderStatuses::negativeStatusCodes())
            ->whereIn('code', $this->driverDisplayStatusCodes())
            ->orderBy('display_order')
            ->get();

        $nextStatus = $this->resolveNextDriverStatus($currentStatus);

        return [
            'order_id' => $order->order_number,
            'current_status_id' => $currentStatus->id,
            'current_status_code' => $currentStatus->code,
            'current_status' => $this->mapStatusForDriver($currentStatus, $currentStatus, $nextStatus),
            'statuses' => $statuses
                ->map(fn (OrderStatuses $status): array => $this->mapStatusForDriver($status, $currentStatus, $nextStatus))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function updateStatus(Driver $driver, string $orderId, string $status): ?array
    {
        $order = $this->findDriverOrder($driver, $orderId);

        if (! $order) {
            return null;
        }

        $targetStatusCode = $this->mapApiStatusToOrderStatus($status);
        $currentStatus = $order->currentStatus
            ?? OrderStatuses::query()->where('code', $order->current_status_code)->where('status', 1)->first();

        if (! $currentStatus) {
            throw ValidationException::withMessages([
                'status' => ['Current order status is invalid.'],
            ]);
        }

        if (in_array($currentStatus->code, OrderStatuses::negativeStatusCodes(), true) || (bool) $currentStatus->is_final) {
            throw ValidationException::withMessages([
                'status' => ['This order is already in a final state and cannot be updated.'],
            ]);
        }

        $targetStatus = OrderStatuses::query()
            ->where('code', $targetStatusCode)
            ->where('status', 1)
            ->first();

        if (! $targetStatus) {
            throw ValidationException::withMessages([
                'status' => ['Requested status is not configured.'],
            ]);
        }

        if (! in_array($currentStatus->code, $this->allowedCurrentStatuses(), true)) {
            throw ValidationException::withMessages([
                'status' => ['This order is not in a driver-manageable state.'],
            ]);
        }

        if ($currentStatus->code === $targetStatus->code) {
            throw ValidationException::withMessages([
                'status' => ['Order is already in the requested status.'],
            ]);
        }

        if ($targetStatus->sequence < $currentStatus->sequence) {
            throw ValidationException::withMessages([
                'status' => ['Status cannot move backwards.'],
            ]);
        }

        $nextStatus = $this->resolveNextDriverStatus($currentStatus);

        if (! $nextStatus || $nextStatus->id !== $targetStatus->id) {
            throw ValidationException::withMessages([
                'status' => ['Only the next status in the flow can be updated.'],
            ]);
        }

        $additionalData = [
            'driver_id' => $driver->id,
        ];

        $storeVendorId = $order->storeVendorAssignment?->store_vendor_id;
        if ($storeVendorId !== null) {
            $additionalData['store_vendor_id'] = $storeVendorId;
        }

        (new UpdateOrderStatusAction())->execute($order, $targetStatus->code, $additionalData);

        $order->refresh()->load('currentStatus');

        return [
            'id' => $order->order_number,
            'status' => $this->mapOrderStatusToApiStatus((string) $order->current_status_code),
            'status_code' => $order->current_status_code,
            'status_label' => $order->currentStatus?->name ?? $targetStatus->name,
        ];
    }

    private function findDriverOrder(Driver $driver, string $orderId): ?Orders
    {
        return Orders::query()
            ->with(['currentStatus', 'storeVendorAssignment'])
            ->where('order_number', $orderId)
            ->where('driver_id', $driver->id)
            ->where('status', 1)
            ->where('is_cancelled', false)
            ->whereHas('storeVendorAssignment', fn ($query) => $query->whereNotNull('store_id'))
            ->whereHas('items')
            ->first();
    }

    private function resolveNextDriverStatus(OrderStatuses $currentStatus): ?OrderStatuses
    {
        return OrderStatuses::query()
            ->where('status', 1)
            ->whereIn('code', $this->driverDisplayStatusCodes())
            ->where('sequence', '>', $currentStatus->sequence)
            ->orderBy('sequence')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapStatusForDriver(OrderStatuses $status, OrderStatuses $currentStatus, ?OrderStatuses $nextStatus): array
    {
        $isCurrent = $status->id === $currentStatus->id;
        $isCompleted = $status->sequence < $currentStatus->sequence;
        $isNext = $nextStatus !== null && $status->id === $nextStatus->id;

        return [
            'id' => $status->id,
            'code' => $status->code,
            'api_status' => $status->code,
            'name' => $status->name,
            'description' => $status->description,
            'color' => $status->color,
            'display_order' => $status->display_order,
            'sequence' => $status->sequence,
            'is_current' => $isCurrent,
            'is_completed' => $isCompleted,
            'is_clickable' => $isNext,
            'is_disabled' => ! $isNext,
        ];
    }

    private function mapApiStatusToOrderStatus(string $status): string
    {
        return match ($status) {
            'accepted', 'driver_accepted' => 'driver_accepted',
            'driver_at_store' => 'driver_at_store',
            'picked_up', 'driver_picked_up' => 'driver_picked_up',
            'out_for_delivery', 'driver_nearby' => 'driver_nearby',
            'driver_reached' => 'driver_reached',
            'delivered' => 'delivered',
            default => throw ValidationException::withMessages([
                'status' => ['The selected status is invalid.'],
            ]),
        };
    }

    private function mapOrderStatusToApiStatus(string $status): string
    {
        return match ($status) {
            'assigned_to_driver' => 'pending',
            'driver_accepted' => 'accepted',
            'driver_picked_up' => 'picked_up',
            'driver_nearby', 'driver_reached' => 'out_for_delivery',
            default => $status,
        };
    }

    /**
     * @return array<int, string>
     */
    private function driverDisplayStatusCodes(): array
    {
        return [
            'assigned_to_driver',
            'driver_accepted',
            'driver_at_store',
            'driver_picked_up',
            'driver_nearby',
            'driver_reached',
            'delivered',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function allowedCurrentStatuses(): array
    {
        return $this->driverDisplayStatusCodes();
    }
}
