<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Driver;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class DeliveryPlanningService
{
    private const DRIVER_LOCATION_FRESHNESS_MINUTES = 10;

    private const DRIVER_SEARCH_RADIUS_KM = 10;

    private const DRIVER_CANDIDATE_LIMIT = 8;

    private const DEFAULT_ORDER_PREPARATION_MINUTES = 15;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findNearbyVendorsWithDriverPreview(
        float $customerLat,
        float $customerLng,
        int $limit = 10,
        ?int $preparationMinutes = null
    ): array {
        $vendors = User::query()
            ->where('user_role', 'store_vendor')
            ->where('status', 1)
            ->whereNotNull('store_lat')
            ->whereNotNull('store_lng')
            ->get();

        if ($vendors->isEmpty()) {
            return [];
        }

        return $vendors
            ->map(function (User $vendor) use ($customerLat, $customerLng, $preparationMinutes): array {
                return $this->buildVendorPreview($vendor, $customerLat, $customerLng, $preparationMinutes);
            })
            ->sort(function (array $first, array $second): int {
                $firstScore = $first['eta_minutes'] ?? PHP_INT_MAX;
                $secondScore = $second['eta_minutes'] ?? PHP_INT_MAX;

                if ($firstScore === $secondScore) {
                    return $first['distance_km'] <=> $second['distance_km'];
                }

                return $firstScore <=> $secondScore;
            })
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function planDriverAssignmentForOrder(Orders $order): ?array
    {
        $order->loadMissing([
            'deliveryAddress',
            'storeVendorAssignment.storeVendor',
            'items.product',
            'items.cut',
        ]);

        $vendor = $order->storeVendorAssignment?->storeVendor;
        if (! $vendor || $vendor->store_lat === null || $vendor->store_lng === null) {
            return null;
        }

        $customerLat = $order->deliveryAddress?->lat;
        $customerLng = $order->deliveryAddress?->lng;
        $preparationMinutes = $this->estimatePreparationMinutesForOrder($order);

        $vendorToCustomerRoute = null;
        if ($customerLat !== null && $customerLng !== null) {
            $vendorToCustomerRoute = $this->estimateRoute(
                (float) $vendor->store_lat,
                (float) $vendor->store_lng,
                (float) $customerLat,
                (float) $customerLng,
            );
        }

        $bestDriver = $this->findBestDriverForVendor(
            (float) $vendor->store_lat,
            (float) $vendor->store_lng,
            $vendorToCustomerRoute,
            $preparationMinutes,
        );

        if ($bestDriver === null) {
            return null;
        }

        return [
            'driver' => $bestDriver['driver_model'],
            'driver_id' => $bestDriver['driver_id'],
            'vendor_id' => $vendor->id,
            'preparation_minutes' => $preparationMinutes,
            'vendor_to_customer' => $vendorToCustomerRoute,
            'best_driver' => $this->stripInternalFields($bestDriver),
            'dispatch_preview' => $this->buildDispatchPreview($bestDriver, $vendorToCustomerRoute, $preparationMinutes),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildVendorPreview(
        User $vendor,
        float $customerLat,
        float $customerLng,
        ?int $preparationMinutes
    ): array {
        $vendorLat = (float) $vendor->store_lat;
        $vendorLng = (float) $vendor->store_lng;
        $vendorToCustomerRoute = $this->estimateRoute($vendorLat, $vendorLng, $customerLat, $customerLng);
        $bestDriver = $this->findBestDriverForVendor($vendorLat, $vendorLng, $vendorToCustomerRoute, $preparationMinutes);

        return [
            'vendor_id' => $vendor->id,
            'vendor_name' => $vendor->name,
            'store_id' => $vendor->store_id,
            'location' => $vendor->location,
            'store_lat' => $vendorLat,
            'store_lng' => $vendorLng,
            'air_distance_km' => $vendorToCustomerRoute['air_distance_km'],
            'distance_km' => $vendorToCustomerRoute['distance_km'],
            'eta_minutes' => $vendorToCustomerRoute['eta_minutes'],
            'has_google_estimate' => $vendorToCustomerRoute['has_google_estimate'],
            'best_driver' => $bestDriver ? $this->stripInternalFields($bestDriver) : null,
            'dispatch_preview' => $this->buildDispatchPreview($bestDriver, $vendorToCustomerRoute, $preparationMinutes),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $vendorToCustomerRoute
     * @return array<string, mixed>|null
     */
    private function findBestDriverForVendor(
        float $vendorLat,
        float $vendorLng,
        ?array $vendorToCustomerRoute,
        ?int $preparationMinutes
    ): ?array {
        $drivers = $this->candidateDrivers();

        if ($drivers->isEmpty()) {
            return null;
        }

        return $drivers
            ->map(function (Driver $driver) use ($vendorLat, $vendorLng, $vendorToCustomerRoute, $preparationMinutes): ?array {
                $routeToVendor = $this->estimateRoute(
                    (float) $driver->current_lat,
                    (float) $driver->current_lng,
                    $vendorLat,
                    $vendorLng,
                );

                if ($routeToVendor['air_distance_km'] > self::DRIVER_SEARCH_RADIUS_KM) {
                    return null;
                }

                $etaToVendor = $routeToVendor['eta_minutes'];
                $pickupEta = $etaToVendor;
                if ($preparationMinutes !== null && $etaToVendor !== null) {
                    $pickupEta = max($preparationMinutes, $etaToVendor);
                }
                $deliveryEta = $pickupEta !== null && ($vendorToCustomerRoute['eta_minutes'] ?? null) !== null
                    ? $pickupEta + $vendorToCustomerRoute['eta_minutes']
                    : null;
                $pickupGap = $preparationMinutes !== null && $etaToVendor !== null
                    ? abs($etaToVendor - $preparationMinutes)
                    : null;
                $driverWait = $preparationMinutes !== null && $etaToVendor !== null
                    ? max($preparationMinutes - $etaToVendor, 0)
                    : null;
                $driverDelay = $preparationMinutes !== null && $etaToVendor !== null
                    ? max($etaToVendor - $preparationMinutes, 0)
                    : null;

                return [
                    'driver_model' => $driver,
                    'driver_id' => $driver->id,
                    'driver_name' => $driver->name,
                    'mobile' => $driver->mobile,
                    'current_lat' => (float) $driver->current_lat,
                    'current_lng' => (float) $driver->current_lng,
                    'distance_to_vendor_km' => $routeToVendor['distance_km'],
                    'air_distance_to_vendor_km' => $routeToVendor['air_distance_km'],
                    'eta_to_vendor_minutes' => $etaToVendor,
                    'has_google_estimate' => $routeToVendor['has_google_estimate'],
                    'estimated_pickup_minutes' => $pickupEta,
                    'estimated_delivery_minutes' => $deliveryEta,
                    'pickup_gap_minutes' => $pickupGap,
                    'driver_wait_minutes' => $driverWait,
                    'driver_delay_minutes' => $driverDelay,
                    'location_updated_at' => ($driver->location_updated_at ?? $driver->updated_at)?->toDateTimeString(),
                    'dispatch_score' => $this->scoreDriverCandidate($routeToVendor, $preparationMinutes, $vendorToCustomerRoute),
                ];
            })
            ->filter()
            ->sortBy('dispatch_score')
            ->take(self::DRIVER_CANDIDATE_LIMIT)
            ->first();
    }

    /**
     * @return Collection<int, Driver>
     */
    private function candidateDrivers(): Collection
    {
        $freshSince = now()->subMinutes(self::DRIVER_LOCATION_FRESHNESS_MINUTES);

        return Driver::query()
            ->where('status', 1)
            ->where('is_available', true)
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->where(function (Builder $query) use ($freshSince): void {
                $query->where('location_updated_at', '>=', $freshSince)
                    ->orWhere(function (Builder $fallback) use ($freshSince): void {
                        $fallback->whereNull('location_updated_at')
                            ->where('updated_at', '>=', $freshSince);
                    });
            })
            ->whereDoesntHave('orders', function (Builder $query): void {
                $query->where('status', 1)
                    ->where('is_cancelled', false)
                    ->whereNotIn('current_status_code', $this->terminalStatusCodes());
            })
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function estimateRoute(float $originLat, float $originLng, float $destinationLat, float $destinationLng): array
    {
        $airDistanceKm = calculate_haversine_distance_km($originLat, $originLng, $destinationLat, $destinationLng);
        $googleEstimate = fetch_google_eta_minutes($originLat, $originLng, $destinationLat, $destinationLng);

        return [
            'air_distance_km' => round($airDistanceKm, 2),
            'distance_km' => $googleEstimate['distance_km'] ?? round($airDistanceKm, 2),
            'eta_minutes' => $googleEstimate['eta_minutes'] ?? null,
            'has_google_estimate' => $googleEstimate !== null,
        ];
    }

    private function estimatePreparationMinutesForOrder(Orders $order): int
    {
        if ($order->relationLoaded('items') && $order->items->isEmpty()) {
            return self::DEFAULT_ORDER_PREPARATION_MINUTES;
        }

        $perItemMinutes = $order->items
            ->map(function ($item): int {
                $cutPreparation = $item->cut?->preparation_time;
                if (is_numeric($cutPreparation)) {
                    return (int) $cutPreparation;
                }

                $productPreparation = $item->product?->preparation_time;
                if (is_numeric($productPreparation)) {
                    return (int) $productPreparation;
                }

                return 0;
            })
            ->filter(static fn (int $minutes): bool => $minutes > 0)
            ->values();

        if ($perItemMinutes->isEmpty()) {
            return self::DEFAULT_ORDER_PREPARATION_MINUTES;
        }

        $maxPreparation = $perItemMinutes->max();
        $buffer = max(0, $perItemMinutes->count() - 1) * 2;

        return (int) ($maxPreparation + $buffer);
    }

    /**
     * @param  array<string, mixed>|null  $bestDriver
     * @param  array<string, mixed>|null  $vendorToCustomerRoute
     * @return array<string, int|null>
     */
    private function buildDispatchPreview(?array $bestDriver, ?array $vendorToCustomerRoute, ?int $preparationMinutes): array
    {
        return [
            'estimated_preparation_minutes' => $preparationMinutes,
            'driver_to_vendor_eta_minutes' => $bestDriver['eta_to_vendor_minutes'] ?? null,
            'estimated_pickup_minutes' => $bestDriver['estimated_pickup_minutes'] ?? null,
            'vendor_to_customer_eta_minutes' => $vendorToCustomerRoute['eta_minutes'] ?? null,
            'estimated_delivery_minutes' => $bestDriver['estimated_delivery_minutes'] ?? null,
            'driver_wait_minutes' => $bestDriver['driver_wait_minutes'] ?? null,
            'driver_delay_minutes' => $bestDriver['driver_delay_minutes'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $routeToVendor
     * @param  array<string, mixed>|null  $vendorToCustomerRoute
     */
    private function scoreDriverCandidate(array $routeToVendor, ?int $preparationMinutes, ?array $vendorToCustomerRoute): float
    {
        $etaToVendor = $routeToVendor['eta_minutes'];

        if ($preparationMinutes === null || $etaToVendor === null) {
            return (float) ($routeToVendor['distance_km'] ?? $routeToVendor['air_distance_km']);
        }

        $pickupGap = abs($etaToVendor - $preparationMinutes);
        $latePenalty = max($etaToVendor - $preparationMinutes, 0) * 3;
        $waitPenalty = max($preparationMinutes - $etaToVendor, 0);
        $deliveryTail = (float) ($vendorToCustomerRoute['eta_minutes'] ?? 0);

        return $pickupGap + $latePenalty + $waitPenalty + $deliveryTail;
    }

    /**
     * @param  array<string, mixed>  $candidate
     * @return array<string, mixed>
     */
    private function stripInternalFields(array $candidate): array
    {
        unset($candidate['driver_model'], $candidate['dispatch_score']);

        return $candidate;
    }

    /**
     * @return array<int, string>
     */
    private function terminalStatusCodes(): array
    {
        return [
            ...\App\Models\OrderStatuses::negativeStatusCodes(),
            'completed',
            'delivered',
        ];
    }
}
