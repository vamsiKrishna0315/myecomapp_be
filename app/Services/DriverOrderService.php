<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Driver;
use App\Models\Orders;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

final class DriverOrderService
{
    public function getDashboardOrders(Driver $driver): array
    {
        $orders = $this->baseOrderQuery($driver)
            ->with([
                'deliveryAddress',
                'items',
                'storeVendorAssignment.store.contactInfo',
                'currentStatus',
            ])
            ->orderByDesc('created_at')
            ->get();

        Log::info('Orders', ['orders' => $orders]);

        return [
            'pending' => $this->mapDashboardOrders($orders, 'pending'),
            'active' => $this->mapDashboardOrders($orders, 'active'),
            'completed' => $this->mapDashboardOrders($orders, 'completed'),
        ];
    }

    public function getOrderDetails(Driver $driver, string $orderId): ?array
    {
        $order = $this->baseOrderQuery($driver)
            ->with([
                'customer',
                'deliveryAddress',
                'billingType',
                'items',
                'storeVendorAssignment.store.contactInfo',
                'currentStatus',
            ])
            ->where('order_number', $orderId)
            ->first();

        if (! $order) {
            return null;
        }

        $store = $order->storeVendorAssignment?->store;
        $assignment = $order->storeVendorAssignment;
        $contact = $store?->contactInfo;
        $deliveryAddress = $order->deliveryAddress;
        $customer = $order->customer;

        return [
            'id' => $order->order_number,
            'status' => $this->normalizeDriverStatus((string) $order->current_status_code),
            'status_label' => $this->statusLabel((string) $order->current_status_code),
            'created_at' => $order->created_at?->format('M d, Y h:i A'),
            'payment_method' => $order->billingType?->name ?? 'N/A',
            'total_amount' => $this->formatMoney($order->total_amount),
            'store_id' => $assignment?->store_id,
            'store_vendor_id' => $this->resolveStoreVendorId($order),
            'store_name' => $store?->name ?? 'N/A',
            'store_address' => $this->buildStoreAddress($store),
            'store_phone' => $contact?->phone ?? $store?->phone ?? '',
            'customer_name' => mb_trim((string) ($customer?->full_name ?? '')) ?: 'Customer',
            'customer_phone' => $customer?->mobile ?? '',
            'delivery_address' => $deliveryAddress?->full_address ?? '',
            'delivery_notes' => $order->special_instructions,
            'delivery_lat' => $deliveryAddress?->lat !== null ? (float) $deliveryAddress->lat : null,
            'delivery_lng' => $deliveryAddress?->lng !== null ? (float) $deliveryAddress->lng : null,
            'products' => $order->items->map(fn ($item): array => [
                'id' => $item->id,
                'name' => mb_trim($item->product_name.' '.$item->cut_name),
                'quantity' => $this->formatQuantity($item->ordered_weight, $item->weight_unit),
                'price' => $this->formatMoney($item->line_total),
            ])->values()->all(),
        ];
    }

    private function mapDashboardOrders(Collection $orders, string $bucket): array
    {
        return $orders
            ->filter(fn (Orders $order): bool => $this->bucketForStatus((string) $order->current_status_code) === $bucket)
            ->map(function (Orders $order) use ($bucket): array {
                $assignment = $order->storeVendorAssignment;
                $store = $order->storeVendorAssignment?->store;
                $deliveryAddress = $order->deliveryAddress;

                $payload = [
                    'id' => $order->order_number,
                    'store_id' => $assignment?->store_id,
                    'store_vendor_id' => $this->resolveStoreVendorId($order),
                    'store_name' => $store?->name ?? 'N/A',
                    'product_summary' => $this->productSummary($order),
                    'pickup_address' => $this->buildStoreAddress($store),
                    'delivery_address' => $deliveryAddress?->full_address ?? '',
                    'delivery_lat' => $deliveryAddress?->lat !== null ? (float) $deliveryAddress->lat : null,
                    'delivery_lng' => $deliveryAddress?->lng !== null ? (float) $deliveryAddress->lng : null,
                    'status' => $this->normalizeDriverStatus((string) $order->current_status_code),
                ];

                if ($bucket === 'active') {
                    $payload['status_label'] = $this->statusLabel((string) $order->current_status_code);
                }

                if ($bucket === 'completed') {
                    $payload['completed_at'] = $order->delivered_at?->diffForHumans() ?? $order->updated_at?->diffForHumans();
                }

                return $payload;
            })
            ->values()
            ->all();
    }

    private function baseOrderQuery(Driver $driver)
    {
        return Orders::query()
            ->where('driver_id', $driver->id)
            ->where('status', 1)
            ->whereHas('storeVendorAssignment', fn ($query) => $query->whereNotNull('store_id'))
            ->whereHas('items');
    }

    private function bucketForStatus(string $status): string
    {
        return match ($status) {
            'delivered', 'completed' => 'completed',
            'assigned_to_driver', 'pending' => 'pending',
            default => 'active',
        };
    }

    private function normalizeDriverStatus(string $status): string
    {
        return match ($status) {
            'assigned_to_driver' => 'pending',
            'driver_accepted' => 'accepted',
            'driver_at_store' => 'accepted',
            'driver_picked_up' => 'picked_up',
            'driver_nearby', 'driver_reached' => 'out_for_delivery',
            default => $status,
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'assigned_to_driver' => 'Pending',
            'driver_accepted' => 'Accepted',
            'driver_at_store' => 'At Store',
            'driver_picked_up' => 'Picked Up',
            'driver_nearby' => 'Near Customer',
            'driver_reached' => 'Reached Customer',
            'out_for_delivery' => 'Out for Delivery',
            'picked_up' => 'Picked Up',
            'accepted' => 'Accepted',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            default => str((string) $status)->replace('_', ' ')->title()->toString(),
        };
    }

    private function productSummary(Orders $order): string
    {
        $summary = $order->items
            ->take(3)
            ->map(fn ($item): string => mb_trim($item->product_name.' '.$item->cut_name))
            ->filter()
            ->implode(', ');

        if ($summary === '') {
            return 'No products';
        }

        $remaining = $order->items->count() - min($order->items->count(), 3);

        if ($remaining > 0) {
            return $summary.', +'.$remaining.' more';
        }

        return $summary;
    }

    private function resolveStoreVendorId(Orders $order): ?int
    {
        $assignment = $order->storeVendorAssignment;

        if (! $assignment) {
            return null;
        }

        return $assignment->store_vendor_id ?? $assignment->store_id;
    }

    private function buildStoreAddress($store): string
    {
        if (! $store) {
            return '';
        }

        return implode(', ', array_filter([
            $store->address_1,
            $store->address_2,
            $store->city,
            $store->state,
            $store->pincode,
            $store->country,
        ]));
    }

    private function formatQuantity($weight, ?string $unit): string
    {
        if ($weight === null) {
            return '1';
        }

        $value = mb_rtrim(mb_rtrim(number_format((float) $weight, 2, '.', ''), '0'), '.');

        return mb_trim($value.' '.($unit ?? ''));
    }

    private function formatMoney($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
