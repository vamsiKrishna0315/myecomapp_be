<?php

declare(strict_types=1);

use App\Models\Orders;
use App\Services\Payments\Gateways\PaytmPayment;
use App\Services\Payments\Gateways\PhonePePayment;
use App\Services\Payments\Gateways\RazorpayPayment;
use App\Services\Payments\PaymentManager;
use InvalidArgumentException;

it('returns razorpay gateway instance', function (): void {
    $gateway = PaymentManager::gateway('razorpay');

    expect($gateway)->toBeInstanceOf(RazorpayPayment::class);
});

it('returns phonepe gateway instance', function (): void {
    $gateway = PaymentManager::gateway('phonepe');

    expect($gateway)->toBeInstanceOf(PhonePePayment::class);
});

it('returns paytm gateway instance', function (): void {
    $gateway = PaymentManager::gateway('paytm');

    expect($gateway)->toBeInstanceOf(PaytmPayment::class);
});

it('throws exception for unsupported provider', function (): void {
    PaymentManager::gateway('stripe');
})->throws(InvalidArgumentException::class);

it('creates phonepe payload using order data', function (): void {
    config()->set('services.phonepe.merchant_id', 'merchant_123');
    config()->set('services.phonepe.callback_url', 'https://example.com/payment/callback');

    $order = new Orders;
    $order->id = 1001;
    $order->customer_id = 501;
    $order->total_amount = 499.99;

    $payload = (new PhonePePayment)->createOrder($order);

    expect($payload)
        ->toBeArray()
        ->and($payload['merchantId'])->toBe('merchant_123')
        ->and($payload['merchantUserId'])->toBe('501')
        ->and($payload['amount'])->toBe(49999)
        ->and($payload['redirectUrl'])->toBe('https://example.com/payment/callback')
        ->and($payload['paymentInstrument']['type'])->toBe('PAY_PAGE');
});

it('creates paytm payload with checksum hash', function (): void {
    config()->set('services.paytm.mid', 'MID123');
    config()->set('services.paytm.key', 'secret_key');
    config()->set('services.paytm.website', 'WEBSTAGING');
    config()->set('services.paytm.callback_url', 'https://example.com/paytm/callback');

    $order = new Orders;
    $order->id = 2002;
    $order->customer_id = 701;
    $order->total_amount = 1500.50;

    $payload = (new PaytmPayment)->createOrder($order);

    expect($payload)
        ->toBeArray()
        ->and($payload['MID'])->toBe('MID123')
        ->and($payload['ORDER_ID'])->toBe('2002')
        ->and($payload['TXN_AMOUNT'])->toBe('1500.50')
        ->and($payload['CALLBACK_URL'])->toBe('https://example.com/paytm/callback')
        ->and($payload['CHECKSUMHASH'])->not->toBeEmpty();
});
