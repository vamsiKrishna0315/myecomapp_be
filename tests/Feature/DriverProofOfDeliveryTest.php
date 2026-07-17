<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\ProofOfDelivery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

function createOrderForProofOfDelivery(Driver $driver, string $statusCode = 'driver_picked_up'): Orders
{
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

    $status = OrderStatuses::query()->where('code', $statusCode)->firstOrFail();

    return Orders::withoutEvents(function () use ($customer, $address, $driver, $status) {
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
}

it('uploads a proof-of-delivery photo for the driver order', function (): void {
    Http::fake(fn () => Http::response([], 200));

    $driver = Driver::query()->create([
        'name' => 'POD Driver',
        'mobile' => '9000000401',
        'email' => 'pod-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 'Bike',
        'vehicle_number' => 'AP01BB0401',
        'license_number' => 'TRACE401',
        'is_available' => true,
        'status' => 1,
    ]);

    $order = createOrderForProofOfDelivery($driver);

    $token = auth('driver-api')->login($driver);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->post('/api/driver/orders/'.$order->order_number.'/proof-of-delivery', [
            'image' => UploadedFile::fake()->image('delivery.jpg'),
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.order_id', $order->order_number);

    $proofOfDelivery = ProofOfDelivery::query()->where('order_id', $order->id)->first();
    expect($proofOfDelivery)->not->toBeNull();
    expect($proofOfDelivery->driver_id)->toBe($driver->id);
    expect($proofOfDelivery->status_code)->toBe('driver_picked_up');
    expect($proofOfDelivery->image_path)->toStartWith('proof-of-delivery/');
});

it('rejects a non-image upload', function (): void {
    $driver = Driver::query()->create([
        'name' => 'Invalid Upload Driver',
        'mobile' => '9000000402',
        'email' => 'invalid-upload-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 'Bike',
        'vehicle_number' => 'AP01BB0402',
        'license_number' => 'TRACE402',
        'is_available' => true,
        'status' => 1,
    ]);

    $order = createOrderForProofOfDelivery($driver);

    $token = auth('driver-api')->login($driver);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->post('/api/driver/orders/'.$order->order_number.'/proof-of-delivery', [
            'image' => UploadedFile::fake()->create('notes.txt', 10),
        ]);

    $response->assertStatus(422);
});

it('returns not found for an order the driver does not own', function (): void {
    Http::fake(fn () => Http::response([], 200));

    $driver = Driver::query()->create([
        'name' => 'Owner Driver',
        'mobile' => '9000000403',
        'email' => 'owner-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 'Bike',
        'vehicle_number' => 'AP01BB0403',
        'license_number' => 'TRACE403',
        'is_available' => true,
        'status' => 1,
    ]);

    $otherDriver = Driver::query()->create([
        'name' => 'Other Driver',
        'mobile' => '9000000404',
        'email' => 'other-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 'Bike',
        'vehicle_number' => 'AP01BB0404',
        'license_number' => 'TRACE404',
        'is_available' => true,
        'status' => 1,
    ]);

    $order = createOrderForProofOfDelivery($otherDriver);

    $token = auth('driver-api')->login($driver);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->post('/api/driver/orders/'.$order->order_number.'/proof-of-delivery', [
            'image' => UploadedFile::fake()->image('delivery.jpg'),
        ]);

    $response->assertNotFound();
});
