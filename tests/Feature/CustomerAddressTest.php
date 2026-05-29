<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Customer;

it('updates an authenticated customer address', function () {
    $customer = Customer::create([
        'first_name' => 'Test',
        'last_name' => 'Customer',
        'email' => 'customer@example.com',
        'mobile' => '9999999999',
        'password' => bcrypt('password'),
        'status' => 1,
    ]);

    $address = Address::create([
        'customer_id' => $customer->id,
        'address_line1' => 'Old line',
        'address_line2' => 'Old second line',
        'city' => 'Old City',
        'state' => 'Old State',
        'zip_code' => '000000',
        'country' => 'Old Country',
        'address_type' => 1,
        'is_default' => false,
        'status' => 1,
    ]);

    $token = auth('customer-api')->login($customer);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/customer/address/{$address->id}", [
            'address_line1' => 'Brt colony',
            'address_line2' => 'line 2, akp, andhra pradesh, 531001',
            'city' => 'akps',
            'state' => 'andhra pradesh',
            'country' => 'India',
            'zip_code' => '531001',
            'address_type' => 1,
            'is_default' => true,
            'status' => 1,
            'customer_id' => $customer->id,
        ]);

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'success' => true,
        'message' => 'Address updated successfully',
        'address_line1' => 'Brt colony',
        'city' => 'akps',
        'state' => 'andhra pradesh',
        'country' => 'India',
        'zip_code' => '531001',
        'address_type' => 1,
        'is_default' => true,
    ]);

    $this->assertDatabaseHas('addresses', [
        'id' => $address->id,
        'address_line1' => 'Brt colony',
        'city' => 'akps',
        'zip_code' => '531001',
        'is_default' => true,
    ]);
});

it('creates an authenticated customer address', function () {
    $customer = Customer::create([
        'first_name' => 'Test',
        'last_name' => 'Customer',
        'email' => 'customer2@example.com',
        'mobile' => '8888888888',
        'password' => bcrypt('password'),
        'status' => 1,
    ]);

    $token = auth('customer-api')->login($customer);

    $payload = [
        'customer_id' => $customer->id,
        'address_line1' => "Hyderabad\nHyderabad",
        'address_line2' => '',
        'city' => 'Kothaguda',
        'state' => 'andhra pradesh',
        'country' => 'India',
        'zip_code' => '500084',
        'address_type' => 1,
        'is_default' => false,
        'status' => 1,
        'lat' => null,
        'lng' => null,
        'google_places_data' => null,
    ];

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/customer/address', $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment([
        'success' => true,
        'message' => 'Address created successfully',
        'address_line1' => "Hyderabad\nHyderabad",
        'city' => 'Kothaguda',
        'state' => 'andhra pradesh',
        'country' => 'India',
        'zip_code' => '500084',
        'address_type' => 1,
    ]);

    $this->assertDatabaseHas('addresses', [
        'customer_id' => $customer->id,
        'address_line1' => "Hyderabad\nHyderabad",
        'city' => 'Kothaguda',
        'zip_code' => '500084',
    ]);
});
