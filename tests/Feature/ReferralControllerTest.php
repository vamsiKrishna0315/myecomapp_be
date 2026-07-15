<?php

declare(strict_types=1);

use App\Models\Customer;

it('generates and reuses a referral link for the authenticated customer', function (): void {
    $customer = Customer::factory()->create();
    $this->actingAs($customer, 'customer-api');

    $first = $this->postJson('/api/v1/customer/referral/generate');
    $first->assertOk()->assertJsonStructure(['data' => ['code', 'discount', 'share_url']]);

    $second = $this->postJson('/api/v1/customer/referral/generate');

    expect($second->json('data.code'))->toBe($first->json('data.code'));
});

it('rejects generating a referral link when unauthenticated', function (): void {
    $this->postJson('/api/v1/customer/referral/generate')->assertUnauthorized();
});

it('reports a referral code as eligible for another customer', function (): void {
    $owner = Customer::factory()->create();
    $this->actingAs($owner, 'customer-api');
    $code = $this->postJson('/api/v1/customer/referral/generate')->json('data.code');

    $referred = Customer::factory()->create();
    $this->actingAs($referred, 'customer-api');

    $response = $this->getJson('/api/v1/customer/referral/check?code='.$code);

    $response->assertOk()->assertJson(['data' => ['eligible' => true]]);
});

it('blocks self-referral eligibility checks', function (): void {
    $owner = Customer::factory()->create();
    $this->actingAs($owner, 'customer-api');
    $code = $this->postJson('/api/v1/customer/referral/generate')->json('data.code');

    $response = $this->getJson('/api/v1/customer/referral/check?code='.$code);

    $response->assertStatus(400)->assertJson(['message' => 'You cannot use your own referral code.']);
});

it('allows a guest to check an eligible referral code', function (): void {
    $owner = Customer::factory()->create();
    $this->actingAs($owner, 'customer-api');
    $code = $this->postJson('/api/v1/customer/referral/generate')->json('data.code');

    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/customer/referral/check?code='.$code);

    $response->assertOk()->assertJson(['data' => ['eligible' => true]]);
});

it('returns the authenticated customer analytics for their own referral code', function (): void {
    $customer = Customer::factory()->create();
    $this->actingAs($customer, 'customer-api');
    $code = $this->postJson('/api/v1/customer/referral/generate')->json('data.code');

    $response = $this->getJson('/api/v1/customer/referral/analytics');

    $response->assertOk()->assertJson([
        'data' => [
            'code' => $code,
            'total_orders' => 0,
            'successful_orders' => 0,
            'pending_orders' => 0,
        ],
    ]);
});
