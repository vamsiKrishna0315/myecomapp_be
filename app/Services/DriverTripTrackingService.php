<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverTripLocation;
use App\Models\Orders;
use Illuminate\Support\Collection;

final class DriverTripTrackingService
{
    /**
     * @return array<string, int>
     */
    public function updateDriverLocation(Driver $driver, float $lat, float $lng, ?float $accuracyMeters = null): array
    {
        $driver->forceFill([
            'current_lat' => $lat,
            'current_lng' => $lng,
            'location_updated_at' => now(),
        ])->save();

        $trackedOrders = $this->trackableOrders($driver);
        $recordedTrips = 0;

        foreach ($trackedOrders as $order) {
            $this->storeTripBreadcrumb($driver, $order, $lat, $lng, $accuracyMeters);
            $recordedTrips++;
        }

        return [
            'updated_driver' => 1,
            'tracked_orders' => $trackedOrders->count(),
            'recorded_trip_points' => $recordedTrips,
        ];
    }

    public function buildDriverMapPayload(Driver $driver, string $orderId): ?array
    {
        $order = Orders::query()
            ->with([
                'deliveryAddress',
                'customer',
                'billingType',
                'items',
                'storeVendorAssignment.store',
                'storeVendorAssignment.storeVendor',
                'currentStatus',
                'tripLocations' => fn ($query) => $query->orderByDesc('recorded_at')->limit(120),
            ])
            ->where('driver_id', $driver->id)
            ->where('status', 1)
            ->where('order_number', $orderId)
            ->first();

        if (! $order) {
            return null;
        }

        return $this->buildTripPayload($order);
    }

    public function buildCustomerTrackingPayload(Orders $order): array
    {
        $order->loadMissing([
            'deliveryAddress',
            'customer',
            'driver',
            'storeVendorAssignment.storeVendor',
            'tripLocations' => fn ($query) => $query->orderByDesc('recorded_at')->limit(240),
            'currentStatus',
        ]);

        return $this->buildTripPayload($order);
    }

    /**
     * @return Collection<int, Orders>
     */
    private function trackableOrders(Driver $driver): Collection
    {
        return Orders::query()
            ->with('storeVendorAssignment.storeVendor')
            ->where('driver_id', $driver->id)
            ->where('status', 1)
            ->where('is_cancelled', false)
            ->whereIn('current_status_code', $this->trackableStatusCodes())
            ->get();
    }

    private function storeTripBreadcrumb(
        Driver $driver,
        Orders $order,
        float $lat,
        float $lng,
        ?float $accuracyMeters = null
    ): DriverTripLocation {
        return DriverTripLocation::query()->create([
            'order_id' => $order->id,
            'driver_id' => $driver->id,
            'lat' => $lat,
            'lng' => $lng,
            'accuracy_meters' => $accuracyMeters,
            'source_status_code' => $order->current_status_code,
            'trip_phase' => $this->tripPhase((string) $order->current_status_code),
            'recorded_at' => now(),
            'status' => 1,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTripPayload(Orders $order): array
    {
        $vendor = $order->storeVendorAssignment?->storeVendor;
        $store = $order->storeVendorAssignment?->store;
        $deliveryAddress = $order->deliveryAddress;
        $driver = $order->driver;
        $statusCode = (string) $order->current_status_code;

        $driverLat = $driver?->current_lat !== null ? (float) $driver->current_lat : null;
        $driverLng = $driver?->current_lng !== null ? (float) $driver->current_lng : null;
        $deliveryLat = $deliveryAddress?->lat !== null ? (float) $deliveryAddress->lat : null;
        $deliveryLng = $deliveryAddress?->lng !== null ? (float) $deliveryAddress->lng : null;
        $vendorLat = $vendor?->store_lat !== null ? (float) $vendor->store_lat : null;
        $vendorLng = $vendor?->store_lng !== null ? (float) $vendor->store_lng : null;

        $eta = null;
        if ($driverLat !== null && $driverLng !== null && $deliveryLat !== null && $deliveryLng !== null) {
            $eta = fetch_google_eta_minutes($driverLat, $driverLng, $deliveryLat, $deliveryLng);
        }

        return [
            'order' => [
                'id' => $order->order_number,
                'uuid' => $order->uuid,
                'status_code' => $statusCode,
                'status_label' => $this->statusLabel($statusCode),
                'payment_method' => $order->billingType?->name ?? 'N/A',
                'total_amount' => number_format((float) $order->total_amount, 2, '.', ''),
                'customer_name' => mb_trim((string) ($order->customer?->full_name ?? '')) ?: 'Customer',
                'customer_phone' => $order->customer?->mobile ?? '',
                'delivery_address' => $deliveryAddress?->full_address ?? '',
                'delivery_notes' => $order->special_instructions,
                'product_summary' => $order->items
                    ->take(3)
                    ->map(fn ($item): string => mb_trim($item->product_name.' '.$item->cut_name))
                    ->filter()
                    ->implode(', '),
                'is_live_trip' => in_array($statusCode, $this->trackableStatusCodes(), true),
            ],
            'vendor' => [
                'name' => $store?->name ?? $vendor?->name ?? 'Assigned Vendor',
                'lat' => $vendorLat,
                'lng' => $vendorLng,
            ],
            'driver' => [
                'id' => $driver?->id,
                'name' => $driver?->name,
                'lat' => $driverLat,
                'lng' => $driverLng,
                'location_updated_at' => $driver?->location_updated_at?->toIso8601String(),
            ],
            'destination' => [
                'lat' => $deliveryLat,
                'lng' => $deliveryLng,
            ],
            'eta' => [
                'minutes' => $eta['eta_minutes'] ?? null,
                'distance_km' => $eta['distance_km'] ?? null,
                'available' => $eta !== null,
            ],
            'trail' => $order->tripLocations
                ->sortBy('recorded_at')
                ->values()
                ->map(fn (DriverTripLocation $point): array => [
                    'lat' => (float) $point->lat,
                    'lng' => (float) $point->lng,
                    'phase' => $point->trip_phase,
                    'status_code' => $point->source_status_code,
                    'recorded_at' => $point->recorded_at?->toIso8601String(),
                ])->all(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function trackableStatusCodes(): array
    {
        return [
            'assigned_to_driver',
            'driver_accepted',
            'driver_at_store',
            'driver_picked_up',
            'driver_nearby',
            'driver_reached',
            'accepted',
            'picked_up',
            'out_for_delivery',
        ];
    }

    private function tripPhase(string $statusCode): string
    {
        return match ($statusCode) {
            'driver_picked_up', 'driver_nearby', 'driver_reached', 'picked_up', 'out_for_delivery' => 'to_customer',
            default => 'to_vendor',
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'assigned_to_driver' => 'Pending',
            'driver_accepted', 'accepted' => 'Accepted',
            'driver_at_store' => 'At Store',
            'driver_picked_up', 'picked_up' => 'Picked Up',
            'driver_nearby' => 'Near Customer',
            'driver_reached' => 'Reached Customer',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            default => str($status)->replace('_', ' ')->title()->toString(),
        };
    }
}
