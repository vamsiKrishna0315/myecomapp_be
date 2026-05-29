<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Services\Payments\PaymentManager;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

it('creates order successfully when payment creation succeeds', function (): void {
    // Create a customer
    $customer = Customer::factory()->create();

    // Mock the authenticated user
    $this->actingAs($customer, 'customer-api');

    // Set up Razorpay config
    Config::set('services.razorpay.key', 'test_key');
    Config::set('services.razorpay.secret', 'test_secret');

    // Mock PaymentManager to return successful payment
    $mockPayment = ['id' => 'order_test123', 'amount' => 10000, 'currency' => 'INR'];

    $this->mock(PaymentManager::class, function ($mock) use ($mockPayment) {
        $mock->shouldReceive('gateway')
            ->with('razorpay')
            ->andReturnSelf();
        $mock->shouldReceive('createOrderFromData')
            ->once()
            ->andReturn($mockPayment);
    });

    $orderData = [
        'items' => [
            [
                'product_id' => 1,
                'category_id' => 1,
                'cut_id' => 1,
                'unit_price' => 100.00,
                'quantity' => 1.0,
                'total_price' => 100.00,
            ],
        ],
        'delivery_address_id' => 1,
        'billing_address_id' => 1,
        'delivery_date' => now()->addDay()->format('Y-m-d'),
        'delivery_time_slot' => '10:00-12:00',
        'billing_type_id' => 1,
        'provider' => 'razorpay',
    ];

    $response = $this->postJson('/api/v1/customer/order', $orderData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'order' => [
                    'id',
                    'customer_id',
                    'total_amount',
                    'razorpay_order_id',
                ],
                'payment_provider',
                'payment',
            ],
        ]);

    // Verify order was created
    $this->assertDatabaseHas('orders', [
        'customer_id' => $customer->id,
        'razorpay_order_id' => 'order_test123',
    ]);
});

it('does not create order when payment creation fails', function (): void {
    // Create a customer
    $customer = Customer::factory()->create();

    // Mock the authenticated user
    $this->actingAs($customer, 'customer-api');

    // Set up Razorpay config
    Config::set('services.razorpay.key', 'test_key');
    Config::set('services.razorpay.secret', 'test_secret');

    // Mock PaymentManager to throw exception
    $this->mock(PaymentManager::class, function ($mock) {
        $mock->shouldReceive('gateway')
            ->with('razorpay')
            ->andReturnSelf();
        $mock->shouldReceive('createOrderFromData')
            ->once()
            ->andThrow(new Exception('Payment gateway error'));
    });

    $orderData = [
        'items' => [
            [
                'product_id' => 1,
                'category_id' => 1,
                'cut_id' => 1,
                'unit_price' => 100.00,
                'quantity' => 1.0,
                'total_price' => 100.00,
            ],
        ],
        'delivery_address_id' => 1,
        'billing_address_id' => 1,
        'delivery_date' => now()->addDay()->format('Y-m-d'),
        'delivery_time_slot' => '10:00-12:00',
        'billing_type_id' => 1,
        'provider' => 'razorpay',
    ];

    $response = $this->postJson('/api/v1/customer/order', $orderData);

    $response->assertStatus(500);

    // Verify order was NOT created
    $this->assertDatabaseMissing('orders', [
        'customer_id' => $customer->id,
    ]);
});
