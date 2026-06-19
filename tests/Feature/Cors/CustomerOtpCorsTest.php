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
