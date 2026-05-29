<?php

declare(strict_types=1);

use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\Http;

it('returns nearby vendors ordered by estimated travel time', function () {
    $vendorOne = User::factory()->create([
        'name' => 'Vendor One',
        'user_role' => 'store_vendor',
        'status' => 1,
        'location' => 'North Hub',
        'store_lat' => 12.9716000,
        'store_lng' => 77.5946000,
    ]);

    $vendorTwo = User::factory()->create([
        'name' => 'Vendor Two',
        'user_role' => 'store_vendor',
        'status' => 1,
        'location' => 'South Hub',
        'store_lat' => 12.9616000,
        'store_lng' => 77.5846000,
    ]);

    $driverOne = Driver::query()->create([
        'name' => 'Driver One',
        'mobile' => '9000000001',
        'email' => 'driver-one@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 1,
        'vehicle_number' => 'AP01AA0001',
        'license_number' => 'LIC001',
        'current_lat' => 12.9661000,
        'current_lng' => 77.5861000,
        'location_updated_at' => now(),
        'is_available' => true,
        'status' => 1,
    ]);

    $driverTwo = Driver::query()->create([
        'name' => 'Driver Two',
        'mobile' => '9000000002',
        'email' => 'driver-two@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 1,
        'vehicle_number' => 'AP01AA0002',
        'license_number' => 'LIC002',
        'current_lat' => 12.9711000,
        'current_lng' => 77.5951000,
        'location_updated_at' => now(),
        'is_available' => true,
        'status' => 1,
    ]);

    config()->set('services.google.maps.api_key', 'test-google-key');

    Http::fake([
        'https://maps.googleapis.com/maps/api/distancematrix/json*' => function ($request) use ($vendorOne, $vendorTwo, $driverOne, $driverTwo) {
            $origins = $request['origins'];

            if ($origins === "{$vendorOne->store_lat},{$vendorOne->store_lng}") {
                return Http::response([
                    'status' => 'OK',
                    'rows' => [[
                        'elements' => [[
                            'status' => 'OK',
                            'distance' => ['value' => 4200],
                            'duration' => ['value' => 780],
                        ]],
                    ]],
                ]);
            }

            if ($origins === "{$vendorTwo->store_lat},{$vendorTwo->store_lng}") {
                return Http::response([
                    'status' => 'OK',
                    'rows' => [[
                        'elements' => [[
                            'status' => 'OK',
                            'distance' => ['value' => 3100],
                            'duration' => ['value' => 420],
                        ]],
                    ]],
                ]);
            }

            if ($origins === "{$driverOne->current_lat},{$driverOne->current_lng}") {
                return Http::response([
                    'status' => 'OK',
                    'rows' => [[
                        'elements' => [[
                            'status' => 'OK',
                            'distance' => ['value' => 1500],
                            'duration' => ['value' => 300],
                        ]],
                    ]],
                ]);
            }

            if ($origins === "{$driverTwo->current_lat},{$driverTwo->current_lng}") {
                return Http::response([
                    'status' => 'OK',
                    'rows' => [[
                        'elements' => [[
                            'status' => 'OK',
                            'distance' => ['value' => 2200],
                            'duration' => ['value' => 660],
                        ]],
                    ]],
                ]);
            }

            return Http::response(['status' => 'INVALID_REQUEST'], 400);
        },
    ]);

    $response = $this->postJson('/api/v1/customer/nearby-vendors', [
        'session-latitude' => 12.9550000,
        'session-longitude' => 77.5800000,
        'estimated_preparation_minutes' => 12,
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.total_vendors', 2)
        ->assertJsonPath('data.estimated_preparation_minutes', 12)
        ->assertJsonPath('data.nearest_vendor.vendor_id', $vendorTwo->id)
        ->assertJsonPath('data.nearest_vendor.eta_minutes', 7)
        ->assertJsonPath('data.nearest_vendor.best_driver.driver_id', $driverTwo->id)
        ->assertJsonPath('data.nearest_vendor.best_driver.eta_to_vendor_minutes', 11)
        ->assertJsonPath('data.nearest_vendor.dispatch_preview.estimated_delivery_minutes', 19)
        ->assertJsonPath('data.vendors.0.vendor_id', $vendorTwo->id)
        ->assertJsonPath('data.vendors.1.vendor_id', $vendorOne->id);
});
