<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\BillingType;
use App\Models\Coupon;
use App\Models\CouponSetting;
use App\Models\Customer;
use App\Models\Orders;
use App\Services\ReferralCouponService;
use Illuminate\Support\Str;

it('creates one referral coupon per customer and reuses it on subsequent calls', function (): void {
    $customer = Customer::factory()->create();
    $service = app(ReferralCouponService::class);

    $first = $service->getOrCreateForCustomer($customer);
    $second = $service->getOrCreateForCustomer($customer);

    $settings = CouponSetting::current();

    expect($first->id)->toBe($second->id)
        ->and(Coupon::where('customer_id', $customer->id)->where('is_referral', true)->count())->toBe(1)
        ->and((int) $first->type)->toBe($settings->referral_discount_type)
        ->and((float) $first->value)->toBe((float) $settings->referral_discount_value)
        ->and((float) $first->min_order_amount)->toBe((float) $settings->referral_min_order_amount)
        ->and($first->max_discount_amount)->toBe($settings->referral_max_discount_amount);
});

it('creates a referral coupon using a fixed-amount setting', function (): void {
    CouponSetting::current()->update([
        'referral_discount_type' => 1, // FixedAmount
        'referral_discount_value' => 50,
        'referral_min_order_amount' => 200,
        'referral_max_discount_amount' => null,
    ]);

    $customer = Customer::factory()->create();
    $coupon = app(ReferralCouponService::class)->getOrCreateForCustomer($customer);

    expect((int) $coupon->type)->toBe(1)
        ->and((float) $coupon->value)->toBe(50.0)
        ->and((float) $coupon->min_order_amount)->toBe(200.0)
        ->and($coupon->max_discount_amount)->toBeNull();
});

it('rejects an invalid coupon code', function (): void {
    $service = app(ReferralCouponService::class);

    $result = $service->checkEligibility('DOES-NOT-EXIST');

    expect($result['eligible'])->toBeFalse()
        ->and($result['reason'])->toBe('Invalid coupon code.');
});

it('rejects an expired coupon', function (): void {
    $customer = Customer::factory()->create();
    $coupon = Coupon::create([
        'customer_id' => $customer->id,
        'is_referral' => true,
        'code' => 'EXPIRED1',
        'type' => 0,
        'value' => 10,
        'usage_per_customer' => 1,
        'valid_from' => now()->subDays(10),
        'valid_until' => now()->subDay(),
        'status' => 1,
    ]);

    $service = app(ReferralCouponService::class);

    $result = $service->checkEligibility($coupon->code);

    expect($result['eligible'])->toBeFalse()
        ->and($result['reason'])->toBe('Coupon has expired.');
});

it('blocks a customer from using their own referral code', function (): void {
    $owner = Customer::factory()->create();
    $service = app(ReferralCouponService::class);
    $coupon = $service->getOrCreateForCustomer($owner);

    $result = $service->checkEligibility($coupon->code, $owner->id);

    expect($result['eligible'])->toBeFalse()
        ->and($result['reason'])->toBe('You cannot use your own referral code.');
});

it('allows another customer to use the referral code', function (): void {
    $owner = Customer::factory()->create();
    $referredCustomer = Customer::factory()->create();
    $service = app(ReferralCouponService::class);
    $coupon = $service->getOrCreateForCustomer($owner);

    $result = $service->checkEligibility($coupon->code, $referredCustomer->id);

    expect($result['eligible'])->toBeTrue()
        ->and($result['coupon']->id)->toBe($coupon->id);
});

it('reports zero analytics when the customer has no referral coupon yet', function (): void {
    $customer = Customer::factory()->create();
    $service = app(ReferralCouponService::class);

    $analytics = $service->getAnalytics($customer);

    expect($analytics)->toBe([
        'code' => null,
        'total_orders' => 0,
        'successful_orders' => 0,
        'pending_orders' => 0,
        'total_discount_given' => 0.0,
    ]);
});

it('counts only paid and delivered orders as successful in analytics', function (): void {
    $owner = Customer::factory()->create();
    $referredCustomer = Customer::factory()->create();
    $service = app(ReferralCouponService::class);
    $coupon = $service->getOrCreateForCustomer($owner);

    $address = Address::create([
        'customer_id' => $referredCustomer->id,
        'address_line1' => '123 Test St',
        'city' => 'Testville',
        'state' => 'TS',
        'zip_code' => '12345',
        'country' => 'IN',
        'address_type' => Address::TYPE_HOME,
        'status' => 1,
    ]);
    $billingType = BillingType::create([
        'slug' => 'test-billing',
        'name' => 'Test Billing',
        'status' => 1,
    ]);

    // Inserted directly (bypassing model events/observers that assign store vendors) -
    // this test only needs the rows to exist for the analytics query, not the full order pipeline.
    Orders::query()->insert([
        'uuid' => (string) Str::uuid(),
        'order_number' => 'ORD-TEST-0001',
        'customer_id' => $referredCustomer->id,
        'delivery_address_id' => $address->id,
        'delivery_date' => now()->addDay(),
        'delivery_time_slot' => '10:00-12:00',
        'billing_type_id' => $billingType->id,
        'subtotal' => 100,
        'coupon_code' => $coupon->code,
        'discount_amount' => 10,
        'discount_type' => 1,
        'tax_percentage' => 18,
        'tax_amount' => 18,
        'delivery_charge' => 0,
        'total_amount' => 108,
        'payment_status' => 1,
        'status' => 1,
        'current_status_id' => 1,
        'current_status_code' => 'delivered',
        'delivered_at' => now(),
        'is_cancelled' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Orders::query()->insert([
        'uuid' => (string) Str::uuid(),
        'order_number' => 'ORD-TEST-0002',
        'customer_id' => $referredCustomer->id,
        'delivery_address_id' => $address->id,
        'delivery_date' => now()->addDay(),
        'delivery_time_slot' => '10:00-12:00',
        'billing_type_id' => $billingType->id,
        'subtotal' => 50,
        'coupon_code' => $coupon->code,
        'discount_amount' => 5,
        'discount_type' => 1,
        'tax_percentage' => 18,
        'tax_amount' => 9,
        'delivery_charge' => 0,
        'total_amount' => 54,
        'payment_status' => 0,
        'status' => 1,
        'current_status_id' => 1,
        'current_status_code' => 'pending',
        'is_cancelled' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $analytics = $service->getAnalytics($owner);

    expect($analytics['code'])->toBe($coupon->code)
        ->and($analytics['total_orders'])->toBe(2)
        ->and($analytics['successful_orders'])->toBe(1)
        ->and($analytics['pending_orders'])->toBe(1)
        ->and($analytics['total_discount_given'])->toBe(10.0);
});
