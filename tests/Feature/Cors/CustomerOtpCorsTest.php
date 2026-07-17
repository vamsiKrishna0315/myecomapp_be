<?php

declare(strict_types=1);

it('allows preflight requests from the customer frontend origin', function (): void {
    $response = $this->withHeaders([
        'Origin' => 'https://myecomapp-fe.vercel.app',
        'Access-Control-Request-Method' => 'POST',
        'Access-Control-Request-Headers' => 'content-type,authorization',
    ])->options('/api/v1/customer/send-otp');

    $response
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', 'https://myecomapp-fe.vercel.app')
        ->assertHeader('Access-Control-Allow-Credentials', 'true');
});

it('allows localhost preflight requests to the protected cart endpoint', function (): void {
    $response = $this->withHeaders([
        'Origin' => 'http://localhost:3000',
        'Access-Control-Request-Method' => 'POST',
        'Access-Control-Request-Headers' => 'content-type,authorization',
    ])->options('/api/v1/customer/cart');

    $response
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
        ->assertHeader('Access-Control-Allow-Credentials', 'true');
});

it('allows preflight requests from the Capacitor-wrapped driver app origin', function (): void {
    $response = $this->withHeaders([
        'Origin' => 'http://localhost',
        'Access-Control-Request-Method' => 'POST',
        'Access-Control-Request-Headers' => 'content-type,authorization',
    ])->options('/api/driver/send-otp');

    $response
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', 'http://localhost')
        ->assertHeader('Access-Control-Allow-Credentials', 'true');
});
