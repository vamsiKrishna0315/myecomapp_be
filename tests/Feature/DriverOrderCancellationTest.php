<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\OrderCancellation;
use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\Store;
use App\Models\StoreVendorOrders;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Queue::fake();
});

function createDriverCancellationOrder(Driver $driver, string $statusCode = 'assigned_to_driver'): Orders
{
    Role::findOrCreate('store_vendor', 'web');

    $customer = Customer::factory()->create();

    $address = Address::query()->create([
        'customer_id' => $customer->id,
        'address_line1' => 'Delivery Street',
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

    $store = Store::query()->create([
        'name' => 'Dispatch Store',
        'phone' => '9000000998',
        'pincode' => '530001',
        'status' => 1,
    ]);

    $vendor = User::factory()->create([
        'name' => 'Dispatch Vendor',
        'user_role' => 'store_vendor',
        'store_id' => $store->id,
        'status' => 1,
    ]);

    $status = OrderStatuses::query()->where('code', $statusCode)->firstOrFail();

    $order = Orders::withoutEvents(function () use ($customer, $address, $driver, $status) {
        return Orders::query()->create([
            'uuid' => (string) Str::uuid(),
            'order_number' => 'ORD-'.now()->year.'-'.mb_str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
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

    StoreVendorOrders::withoutEvents(function () use ($order, $customer, $store, $vendor) {
        return StoreVendorOrders::query()->create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'store_id' => $store->id,
            'store_vendor_id' => $vendor->id,
            'is_eligible' => true,
            'status' => 1,
        ]);
    });

    return $order;
}

it('cancels an order before driver acceptance', function (): void {
    $driver = Driver::query()->create([
        'name' => 'Cancel Driver',
        'mobile' => '9000000301',
        'email' => 'cancel-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 'Bike',
        'vehicle_number' => 'AP01BB0301',
        'license_number' => 'TRACE301',
        'is_available' => true,
        'status' => 1,
    ]);

    $order = createDriverCancellationOrder($driver);

    $token = auth('driver-api')->login($driver);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/driver/orders/'.$order->order_number.'/cancel', [
            'reason' => 'vehicle_issue',
            'notes' => 'Bike broke down',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('requires_customer_confirmation', true);

    $order->refresh();
    expect($order->current_status_code)->toBe('cancelled');
    expect($order->is_cancelled)->toBeTrue();
    expect($order->driver_id)->toBeNull();

    $cancellation = OrderCancellation::query()->where('order_id', $order->id)->first();
    expect($cancellation)->not->toBeNull();
    expect($cancellation->cancelled_by)->toBe('driver');
    expect($cancellation->cancelled_by_id)->toBe($driver->id);
    expect($cancellation->reason_code)->toBe('vehicle_issue');
    expect($cancellation->reason_description)->toBe('Bike broke down');
});

it('rejects cancellation once the order has been accepted', function (): void {
    $driver = Driver::query()->create([
        'name' => 'Late Cancel Driver',
        'mobile' => '9000000302',
        'email' => 'late-cancel-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 'Bike',
        'vehicle_number' => 'AP01BB0302',
        'license_number' => 'TRACE302',
        'is_available' => true,
        'status' => 1,
    ]);

    $order = createDriverCancellationOrder($driver, 'driver_accepted');

    $token = auth('driver-api')->login($driver);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/driver/orders/'.$order->order_number.'/cancel', [
            'reason' => 'other',
        ]);

    $response->assertStatus(422);
    expect($order->fresh()->current_status_code)->toBe('driver_accepted');
});

it('validates the cancellation reason', function (): void {
    $driver = Driver::query()->create([
        'name' => 'Invalid Reason Driver',
        'mobile' => '9000000303',
        'email' => 'invalid-reason-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 'Bike',
        'vehicle_number' => 'AP01BB0303',
        'license_number' => 'TRACE303',
        'is_available' => true,
        'status' => 1,
    ]);

    $order = createDriverCancellationOrder($driver);

    $token = auth('driver-api')->login($driver);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/driver/orders/'.$order->order_number.'/cancel', [
            'reason' => 'not_a_real_reason',
        ]);

    $response->assertStatus(422);
});
