<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Driver;

use App\Actions\Driver\UpdateDriverProfile;
use App\Http\Controllers\Api\V1\ResponseController;
use App\Http\Requests\Api\V1\Driver\UpdateDriverProfileRequest;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DriverProfileController extends ResponseController
{
    public function show(Request $request): JsonResponse
    {
        $driver = $request->user();

        return response()->json($this->formatProfile($driver));
    }

    public function update(UpdateDriverProfileRequest $request): JsonResponse
    {
        $driver = $request->user();
        $data = array_merge($request->validated(), ['driver' => $driver]);
        $updatedDriver = (new UpdateDriverProfile())->execute($data);

        return $this->returnResponse($this->formatProfile($updatedDriver), 'Profile updated successfully');
    }

    /**
     * @return array<string, mixed>
     */
    private function formatProfile(Driver $driver): array
    {
        return [
            'id' => $driver->id,
            'name' => $driver->name,
            'phone' => $driver->mobile,
            'email' => $driver->email,
            'vehicle_type' => $driver->vehicle_type ?? 'Bike',
            'vehicle_number' => $driver->vehicle_number ?? 'Not provided',
            'total_deliveries' => $driver->total_deliveries ?? 0,
            'rating' => $driver->rating !== null ? (string) $driver->rating : '0.0',
        ];
    }
}
