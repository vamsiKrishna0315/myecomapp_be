<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\DriverTripLocation;
use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\OrderStatusTracking;
use App\Models\Store;
use App\Models\StoreVendorOrders;
use App\Models\User;

it('updates driver current location and stores trip breadcrumbs for active orders', function (): void {
    $customer = Customer::factory()->create();

    $address = Address::query()->create([
        'customer_id' => $customer->id,
        'address_line1' => 'Test Street',
        'city' => 'Vizag',
        'state' => 'AP',
        'zip_code' => '530001',
        'country' => 'India',
        'address_type' => 1,
        'is_default' => true,
        'status' => 1,
        'lat' => 17.7200000,
        'lng' => 83.3000000,
    ]);

    $vendor = User::factory()->create([
        'name' => 'Dispatch Vendor',
        'user_role' => 'store_vendor',
        'status' => 1,
        'store_lat' => 17.7100000,
        'store_lng' => 83.2900000,
    ]);

    $store = Store::query()->create([
        'name' => 'Main Store',
        'phone' => '9000000999',
        'pincode' => '530001',
        'status' => 1,
    ]);

    $vendor->update(['store_id' => $store->id]);

    $driver = Driver::query()->create([
        'name' => 'Tracking Driver',
        'mobile' => '9000000151',
        'email' => 'tracking-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 1,
        'vehicle_number' => 'AP01BB0011',
        'license_number' => 'TRACE001',
        'is_available' => true,
        'status' => 1,
    ]);

    $status = OrderStatuses::query()->where('code', 'driver_accepted')->firstOrFail();

    $order = Orders::withoutEvents(function () use ($customer, $address, $driver, $status) {
        return Orders::query()->create([
            'customer_id' => $customer->id,
            'delivery_address_id' => $address->id,
            'billing_address_id' => $address->id,
            'delivery_date' => now()->toDateString(),
            'delivery_time_slot' => '10:00 AM - 12:00 PM',
            'subtotal' => 100,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'delivery_charge' => 0,
            'total_amount' => 100,
            'status' => 1,
            'billing_type_id' => 1,
            'payment_status' => 0,
            'current_status_id' => $status->id,
            'current_status_code' => $status->code,
            'driver_id' => $driver->id,
            'is_cancelled' => false,
        ]);
    });

    StoreVendorOrders::query()->create([
        'order_id' => $order->id,
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'store_vendor_id' => $vendor->id,
        'is_eligible' => true,
        'status' => 1,
    ]);

    $token = auth('driver-api')->login($driver);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/driver/location', [
            'lat' => 17.7054321,
            'lng' => 83.2865432,
            'accuracy_meters' => 9.5,
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('tracked_orders', 1)
        ->assertJsonPath('recorded_trip_points', 1);

    expect($driver->fresh()->current_lat)->toBe('17.7054321');
    expect($driver->fresh()->current_lng)->toBe('83.2865432');

    $tripPoint = DriverTripLocation::query()->where('order_id', $order->id)->first();

    expect($tripPoint)->not->toBeNull();
    expect($tripPoint?->driver_id)->toBe($driver->id);
    expect($tripPoint?->source_status_code)->toBe('driver_accepted');
    expect($tripPoint?->trip_phase)->toBe('to_vendor');
});

it('returns live trip trail in customer tracking endpoint', function (): void {
    $customer = Customer::factory()->create();

    $address = Address::query()->create([
        'customer_id' => $customer->id,
        'address_line1' => 'Customer Street',
        'city' => 'Vizag',
        'state' => 'AP',
        'zip_code' => '530001',
        'country' => 'India',
        'address_type' => 1,
        'is_default' => true,
        'status' => 1,
        'lat' => 17.7200000,
        'lng' => 83.3000000,
    ]);

    $vendor = User::factory()->create([
        'name' => 'Assigned Vendor',
        'user_role' => 'store_vendor',
        'status' => 1,
        'store_lat' => 17.7100000,
        'store_lng' => 83.2900000,
    ]);

    $store = Store::query()->create([
        'name' => 'Store Front',
        'phone' => '9000000888',
        'pincode' => '530001',
        'status' => 1,
    ]);

    $vendor->update(['store_id' => $store->id]);

    $driver = Driver::query()->create([
        'name' => 'Map Driver',
        'mobile' => '9000000152',
        'email' => 'map-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 1,
        'vehicle_number' => 'AP01BB0012',
        'license_number' => 'TRACE002',
        'current_lat' => 17.7060000,
        'current_lng' => 83.2870000,
        'location_updated_at' => now(),
        'is_available' => true,
        'status' => 1,
    ]);

    $status = OrderStatuses::query()->where('code', 'driver_picked_up')->firstOrFail();

    $order = Orders::withoutEvents(function () use ($customer, $address, $driver, $status) {
        return Orders::query()->create([
            'customer_id' => $customer->id,
            'delivery_address_id' => $address->id,
            'billing_address_id' => $address->id,
            'delivery_date' => now()->toDateString(),
            'delivery_time_slot' => '10:00 AM - 12:00 PM',
            'subtotal' => 150,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'delivery_charge' => 0,
            'total_amount' => 150,
            'status' => 1,
            'billing_type_id' => 1,
            'payment_status' => 0,
            'current_status_id' => $status->id,
            'current_status_code' => $status->code,
            'driver_id' => $driver->id,
            'is_cancelled' => false,
        ]);
    });

    StoreVendorOrders::query()->create([
        'order_id' => $order->id,
        'customer_id' => $customer->id,
        'store_id' => $store->id,
        'store_vendor_id' => $vendor->id,
        'is_eligible' => true,
        'status' => 1,
    ]);

    OrderStatusTracking::query()->create([
        'order_id' => $order->id,
        'customer_id' => $customer->id,
        'driver_id' => $driver->id,
        'store_vendor_id' => $vendor->id,
        'order_status_id' => $status->id,
        'status_code' => $status->code,
        'status_name' => $status->name,
        'lat' => 17.7060000,
        'lng' => 83.2870000,
        'description' => 'Driver picked up the order',
        'status' => 1,
    ]);

    DriverTripLocation::query()->create([
        'order_id' => $order->id,
        'driver_id' => $driver->id,
        'lat' => 17.7070000,
        'lng' => 83.2880000,
        'accuracy_meters' => 7,
        'source_status_code' => 'driver_picked_up',
        'trip_phase' => 'to_customer',
        'recorded_at' => now(),
        'status' => 1,
    ]);

    $token = auth('customer-api')->login($customer);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/customer/orders/'.$order->uuid.'/track');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.live_trip.order.id', $order->order_number)
        ->assertJsonPath('data.live_trip.vendor.name', 'Store Front')
        ->assertJsonPath('data.live_trip.trail.0.phase', 'to_customer')
        ->assertJsonPath('data.live_trip.driver.name', 'Map Driver');
});
