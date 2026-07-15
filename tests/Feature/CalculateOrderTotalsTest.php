<?php

declare(strict_types=1);

use App\Actions\Order\CalculateOrderTotals;
use App\Models\Coupon;
use App\Models\Customer;

it('applies a percentage coupon discount correctly', function (): void {
    $customer = Customer::factory()->create();
    Coupon::create([
        'code' => 'PERCENT10',
        'type' => 0, // Percentage
        'value' => 10,
        'usage_per_customer' => 1,
        'valid_from' => now()->subDay(),
        'valid_until' => now()->addDay(),
        'status' => 1,
    ]);

    $totals = app(CalculateOrderTotals::class)->execute([
        'items' => [
            ['total_price' => 100],
        ],
        'coupon_code' => 'PERCENT10',
        'customer_id' => $customer->id,
    ]);

    expect($totals['discount_amount'])->toBe(10.0);
});

it('applies a fixed amount coupon discount correctly', function (): void {
    $customer = Customer::factory()->create();
    Coupon::create([
        'code' => 'FIXED50',
        'type' => 1, // FixedAmount
        'value' => 50,
        'usage_per_customer' => 1,
        'valid_from' => now()->subDay(),
        'valid_until' => now()->addDay(),
        'status' => 1,
    ]);

    $totals = app(CalculateOrderTotals::class)->execute([
        'items' => [
            ['total_price' => 200],
        ],
        'coupon_code' => 'FIXED50',
        'customer_id' => $customer->id,
    ]);

    expect($totals['discount_amount'])->toBe(50.0);
});
