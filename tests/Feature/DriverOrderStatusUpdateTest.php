<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\Product;
use App\Models\ProductCut;
use App\Models\Store;
use App\Models\StoreVendorOrders;
use App\Models\User;

it('updates a driver order status from api status to canonical driver status', function (): void {
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

    $driver = Driver::query()->create([
        'name' => 'Status Driver',
        'mobile' => '9000000153',
        'email' => 'status-driver@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 1,
        'vehicle_number' => 'AP01BB0013',
        'license_number' => 'TRACE003',
        'is_available' => true,
        'status' => 1,
    ]);

    $assignedStatus = OrderStatuses::query()->where('code', 'assigned_to_driver')->firstOrFail();
    $acceptedStatus = OrderStatuses::query()->where('code', 'driver_accepted')->firstOrFail();

    $order = Orders::withoutEvents(function () use ($customer, $address, $driver, $assignedStatus) {
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
            'current_status_id' => $assignedStatus->id,
            'current_status_code' => $assignedStatus->code,
            'driver_id' => $driver->id,
            'is_cancelled' => false,
        ]);
    });

    $category = Category::query()->create([
        'category_name' => 'Seafood',
        'category_type' => 'non_veg',
        'status' => 1,
    ]);

    $product = Product::withoutEvents(function () use ($category) {
        return Product::query()->create([
            'category_id' => $category->id,
            'sku' => 'PROD-DRIVER-001',
            'name' => 'Test Fish',
            'slug' => 'test-fish-driver',
            'price' => 100,
            'status' => 'active',
            'is_visible' => true,
        ]);
    });

    $cut = ProductCut::query()->create([
        'product_id' => $product->id,
        'cut_name' => 'Curry Cut',
        'cut_code' => 'CUT-DRIVER-001',
        'price_per_kg' => 100,
        'minimum_weight' => 1,
        'weight_unit' => 'kg',
        'stock_quantity' => 10,
        'stock_unit' => 'kg',
        'status' => 1,
    ]);

    OrderItems::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'category_id' => $category->id,
        'cut_id' => $cut->id,
        'product_name' => 'Test Fish',
        'cut_name' => 'Curry Cut',
        'sku' => 'PROD-DRIVER-001',
        'price_per_kg' => 100,
        'price_per_piece' => 0,
        'ordered_weight' => 1,
        'actual_weight' => 1,
        'weight_unit' => 'kg',
        'line_subtotal' => 100,
        'line_discount' => 0,
        'line_tax' => 0,
        'line_total' => 100,
        'order_item_status' => 0,
        'status' => 1,
    ]);

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
        ->putJson('/api/driver/orders/'.$order->order_number.'/status', [
            'status' => 'accepted',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('order.id', $order->order_number)
        ->assertJsonPath('order.status', 'accepted')
        ->assertJsonPath('order.status_code', 'driver_accepted');

    expect($order->fresh()->current_status_code)->toBe('driver_accepted');
    expect($order->fresh()->current_status_id)->toBe($acceptedStatus->id);
});
