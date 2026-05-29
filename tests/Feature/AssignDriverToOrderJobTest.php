<?php

declare(strict_types=1);

use App\Jobs\AssignDriverToOrderJob;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\OrderStatusTracking;
use App\Models\Store;
use App\Models\StoreVendorOrders;
use App\Models\User;
use Illuminate\Support\Facades\Http;

it('assigns the driver whose arrival best matches vendor prep time', function (): void {
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
    ]);

    $store = Store::query()->create([
        'name' => 'Main Store',
        'phone' => '9000000999',
        'pincode' => '530001',
        'status' => 1,
    ]);

    $vendor->update([
        'store_id' => $store->id,
        'store_lat' => 17.7100000,
        'store_lng' => 83.2900000,
    ]);

    $fastDriver = Driver::query()->create([
        'name' => 'Fast Driver',
        'mobile' => '9000000101',
        'email' => 'fast-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 1,
        'vehicle_number' => 'AP01BB0001',
        'license_number' => 'FAST001',
        'current_lat' => 17.7090000,
        'current_lng' => 83.2890000,
        'location_updated_at' => now(),
        'is_available' => true,
        'status' => 1,
    ]);

    $alignedDriver = Driver::query()->create([
        'name' => 'Aligned Driver',
        'mobile' => '9000000102',
        'email' => 'aligned-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 1,
        'vehicle_number' => 'AP01BB0002',
        'license_number' => 'ALIGN001',
        'current_lat' => 17.7050000,
        'current_lng' => 83.2850000,
        'location_updated_at' => now(),
        'is_available' => true,
        'status' => 1,
    ]);

    $pendingStatus = OrderStatuses::query()->where('code', 'pending')->firstOrFail();

    $order = Orders::withoutEvents(function () use ($customer, $address, $pendingStatus) {
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
            'current_status_id' => $pendingStatus->id,
            'current_status_code' => $pendingStatus->code,
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

    config()->set('services.google.maps.api_key', 'test-google-key');

    Http::fake([
        'https://maps.googleapis.com/maps/api/distancematrix/json*' => function ($request) use ($vendor, $address, $fastDriver, $alignedDriver) {
            $origins = $request['origins'];
            $destinations = $request['destinations'];

            if ($origins === "{$vendor->store_lat},{$vendor->store_lng}" && $destinations === "{$address->lat},{$address->lng}") {
                return Http::response([
                    'status' => 'OK',
                    'rows' => [[
                        'elements' => [[
                            'status' => 'OK',
                            'distance' => ['value' => 4000],
                            'duration' => ['value' => 900],
                        ]],
                    ]],
                ]);
            }

            if ($origins === "{$fastDriver->current_lat},{$fastDriver->current_lng}") {
                return Http::response([
                    'status' => 'OK',
                    'rows' => [[
                        'elements' => [[
                            'status' => 'OK',
                            'distance' => ['value' => 800],
                            'duration' => ['value' => 180],
                        ]],
                    ]],
                ]);
            }

            if ($origins === "{$alignedDriver->current_lat},{$alignedDriver->current_lng}") {
                return Http::response([
                    'status' => 'OK',
                    'rows' => [[
                        'elements' => [[
                            'status' => 'OK',
                            'distance' => ['value' => 2500],
                            'duration' => ['value' => 840],
                        ]],
                    ]],
                ]);
            }

            return Http::response(['status' => 'INVALID_REQUEST'], 400);
        },
    ]);

    (new AssignDriverToOrderJob($order->id))->handle();

    expect($order->fresh()->driver_id)->toBe($alignedDriver->id);

    $tracking = OrderStatusTracking::query()
        ->where('order_id', $order->id)
        ->where('status_code', 'assigned_to_driver')
        ->first();

    expect($tracking)->not->toBeNull();
    expect($tracking?->driver_id)->toBe($alignedDriver->id);
});
