<?php

declare(strict_types=1);

use App\Models\Driver;

it('returns the authenticated driver profile', function (): void {
    $driver = Driver::query()->create([
        'name' => 'Profile Driver',
        'mobile' => '9000000201',
        'email' => 'profile-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 'Bike',
        'vehicle_number' => 'AP01BB0201',
        'license_number' => 'TRACE201',
        'total_deliveries' => 42,
        'rating' => 4.5,
        'is_available' => true,
        'status' => 1,
    ]);

    $token = auth('driver-api')->login($driver);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/driver/profile');

    $response
        ->assertOk()
        ->assertJsonPath('id', $driver->id)
        ->assertJsonPath('name', 'Profile Driver')
        ->assertJsonPath('phone', '9000000201')
        ->assertJsonPath('vehicle_type', 'Bike')
        ->assertJsonPath('total_deliveries', 42)
        ->assertJsonPath('rating', '4.50');
});

it('updates the authenticated driver profile', function (): void {
    $driver = Driver::query()->create([
        'name' => 'Old Name',
        'mobile' => '9000000202',
        'email' => 'old-name@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 'Bike',
        'vehicle_number' => 'AP01BB0202',
        'license_number' => 'TRACE202',
        'is_available' => true,
        'status' => 1,
    ]);

    $token = auth('driver-api')->login($driver);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/driver/profile', [
            'name' => 'New Name',
            'vehicle_type' => 'Scooter',
            'vehicle_number' => 'AP01BB9999',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.vehicle_type', 'Scooter')
        ->assertJsonPath('data.vehicle_number', 'AP01BB9999');

    expect($driver->fresh()->name)->toBe('New Name');
    expect($driver->fresh()->vehicle_type)->toBe('Scooter');
});

it('rejects a duplicate email on profile update', function (): void {
    Driver::query()->create([
        'name' => 'Existing Driver',
        'mobile' => '9000000203',
        'email' => 'taken@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 'Bike',
        'vehicle_number' => 'AP01BB0203',
        'license_number' => 'TRACE203',
        'is_available' => true,
        'status' => 1,
    ]);

    $driver = Driver::query()->create([
        'name' => 'Another Driver',
        'mobile' => '9000000204',
        'email' => 'another@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 'Bike',
        'vehicle_number' => 'AP01BB0204',
        'license_number' => 'TRACE204',
        'is_available' => true,
        'status' => 1,
    ]);

    $token = auth('driver-api')->login($driver);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/driver/profile', [
            'email' => 'taken@example.com',
        ]);

    $response->assertStatus(422);
});
