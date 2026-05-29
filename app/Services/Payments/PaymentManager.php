<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Services\Payments\Contracts\PaymentInterface;
use App\Services\Payments\Gateways\CodPayment;
use App\Services\Payments\Gateways\PaytmPayment;
use App\Services\Payments\Gateways\PhonePePayment;
use App\Services\Payments\Gateways\RazorpayPayment;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class PaymentManager
{
    public static function gateway(string $provider): PaymentInterface
    {
        Log::info("Selecting payment gateway: {$provider}");

        return match (mb_strtolower(mb_trim($provider))) {
            'razorpay' => new RazorpayPayment(),
            'phonepe' => new PhonePePayment(),
            'paytm' => new PaytmPayment(),
            'cod' => new CodPayment(),
            default => throw new InvalidArgumentException('Unsupported payment provider.'),
        };
    }
}
