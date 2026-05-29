<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Services\DriverTripTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DriverLocationController extends ResponseController
{
    public function __construct(private readonly DriverTripTrackingService $driverTripTrackingService) {}

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'accuracy_meters' => 'nullable|numeric|min:0',
        ]);

        $driver = $request->user();
        $result = $this->driverTripTrackingService->updateDriverLocation(
            $driver,
            (float) $validated['lat'],
            (float) $validated['lng'],
            isset($validated['accuracy_meters']) ? (float) $validated['accuracy_meters'] : null,
        );

        return response()->json([
            'success' => true,
            'message' => 'Driver location updated successfully.',
            'driver' => [
                'lat' => (float) $driver->current_lat,
                'lng' => (float) $driver->current_lng,
                'location_updated_at' => $driver->location_updated_at?->toIso8601String(),
            ],
            'tracked_orders' => $result['tracked_orders'],
            'recorded_trip_points' => $result['recorded_trip_points'],
        ]);
    }

    public function showMap(Request $request, string $orderId): JsonResponse
    {
        $driver = $request->user();
        $payload = $this->driverTripTrackingService->buildDriverMapPayload($driver, $orderId);

        if (! $payload) {
            return response()->json([
                'success' => false,
                'message' => 'Order map data not found.',
            ], 404);
        }

        return response()->json($payload);
    }
}
