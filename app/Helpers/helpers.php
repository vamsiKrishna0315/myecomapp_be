<?php

declare(strict_types=1);

use App\Services\DeliveryPlanningService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

if (! function_exists('format_address_data')) {
    /**
     * Format address model data for API response
     *
     * @param  App\Models\Address  $address
     */
    function format_address_data($address): array
    {
        return [
            'id' => $address->id,
            'type' => $address->address_type_label,
            'address_line_1' => $address->address_line1,
            'address_line_2' => $address->address_line2,
            'city' => $address->city,
            'state' => $address->state,
            'postal_code' => $address->zip_code,
            'country' => $address->country ?? null,
            'is_default' => $address->is_default,
            'full_address' => $address->full_address,
            'created_at' => $address->created_at->format('Y-m-d H:i:s'),
        ];
    }
}

if (! function_exists('format_address_collection')) {
    /**
     * Format collection of addresses for API response
     *
     * @param  Illuminate\Support\Collection  $addresses
     * @return array|Illuminate\Support\Collection
     */
    function format_address_collection($addresses, bool $groupByType = false)
    {
        $formatted = $addresses->map(function ($address) {
            return format_address_data($address);
        });

        if ($groupByType) {
            return $formatted->groupBy('type')->map(function ($group) {
                return $group->values();
            });
        }

        return $formatted;
    }
}

if (! function_exists('format_customer_data')) {
    /**
     * Format customer model data for API response
     *
     * @param  App\Models\Customer  $customer
     */
    function format_customer_data($customer): array
    {
        return [
            'id' => $customer->id,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'full_name' => $customer->full_name,
            'email' => $customer->email,
            'mobile' => $customer->mobile,
            'dob' => $customer->dob?->format('Y-m-d'),
            'status' => $customer->status,
            'created_at' => $customer->created_at->format('Y-m-d H:i:s'),
        ];
    }

    function getStoreVendorForOrder($order)
    {
        $deliveryAddress = $order->deliveryAddress ?? null;

        if (! $deliveryAddress || is_null($deliveryAddress->lat) || is_null($deliveryAddress->lng)) {
            return null;
        }

        return find_nearby_vendors((float) $deliveryAddress->lat, (float) $deliveryAddress->lng, 1)[0] ?? null;
    }
}

if (! function_exists('calculate_haversine_distance_km')) {
    function calculate_haversine_distance_km(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;

        $latFrom = deg2rad($lat1);
        $lngFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lngTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }
}

if (! function_exists('fetch_google_eta_minutes')) {
    function fetch_google_eta_minutes(float $originLat, float $originLng, float $destinationLat, float $destinationLng): ?array
    {
        $apiKey = config('services.google.maps.api_key');

        if (empty($apiKey) || $apiKey === 'your_google_maps_api_key_here') {
            return null;
        }

        try {
            $response = Http::timeout(15)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => "{$originLat},{$originLng}",
                'destinations' => "{$destinationLat},{$destinationLng}",
                'mode' => 'driving',
                'units' => 'metric',
                'key' => $apiKey,
            ]);

            if (! $response->successful()) {
                Log::warning('Google Distance Matrix request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $payload = $response->json();
            $element = $payload['rows'][0]['elements'][0] ?? null;

            if (($payload['status'] ?? null) !== 'OK' || ! $element || ($element['status'] ?? null) !== 'OK') {
                Log::warning('Google Distance Matrix returned invalid status.', [
                    'response_status' => $payload['status'] ?? null,
                    'element_status' => $element['status'] ?? null,
                ]);

                return null;
            }

            $durationSeconds = $element['duration']['value'] ?? null;
            $distanceMeters = $element['distance']['value'] ?? null;

            if (! is_numeric($durationSeconds) || ! is_numeric($distanceMeters)) {
                return null;
            }

            return [
                'eta_minutes' => (int) ceil(((int) $durationSeconds) / 60),
                'distance_km' => round(((int) $distanceMeters) / 1000, 2),
            ];
        } catch (Throwable $exception) {
            Log::error('Google Distance Matrix exception.', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}

if (! function_exists('find_nearby_vendors')) {
    function find_nearby_vendors(
        float $customerLat,
        float $customerLng,
        int $limit = 10,
        ?int $preparationMinutes = null
    ): array {
        return app(DeliveryPlanningService::class)
            ->findNearbyVendorsWithDriverPreview($customerLat, $customerLng, $limit, $preparationMinutes);
    }
}

if (! function_exists('findNearbyDriverWithEstimateTimeToReachDestination')) {
    function findNearbyDriverWithEstimateTimeToReachDestination() {}
}
