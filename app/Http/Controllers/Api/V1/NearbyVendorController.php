<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

final class NearbyVendorController extends ResponseController
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session-latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'session-longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'estimated_preparation_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
        ]);

        $customerLat = $request->input('session-latitude', $request->input('latitude'));
        $customerLng = $request->input('session-longitude', $request->input('longitude'));

        if ($validator->fails() || $customerLat === null || $customerLng === null) {
            $errors = $validator->errors()->toArray();

            if ($customerLat === null) {
                $errors['latitude'] = ['The latitude field is required.'];
            }

            if ($customerLng === null) {
                $errors['longitude'] = ['The longitude field is required.'];
            }

            return $this->sendError('Validation failed.', 422, $errors);
        }

        $customerLat = (float) $customerLat;
        $customerLng = (float) $customerLng;
        $estimatedPreparationMinutes = $request->filled('estimated_preparation_minutes')
            ? (int) $request->integer('estimated_preparation_minutes')
            : null;
        $vendors = find_nearby_vendors($customerLat, $customerLng, 10, $estimatedPreparationMinutes);

        Log::info('Nearby vendors retrieved', [
            'customer_latitude' => $customerLat,
            'customer_longitude' => $customerLng,
            'estimated_preparation_minutes' => $estimatedPreparationMinutes,
            'vendor_count' => count($vendors),
        ]);

        return $this->returnResponse([
            'customer_location' => [
                'lat' => $customerLat,
                'lng' => $customerLng,
            ],
            'estimated_preparation_minutes' => $estimatedPreparationMinutes,
            'nearest_vendor' => $vendors[0] ?? null,
            'vendors' => $vendors,
            'total_vendors' => count($vendors),
        ], 'Nearby vendors retrieved successfully.');
    }
}
