<?php

declare(strict_types=1);

namespace App\Services\Payments\Gateways;

use App\Actions\Order\CalculateOrderTotals;
use App\Models\Orders;
use App\Services\Payments\Contracts\PaymentInterface;
use Razorpay\Api\Api;
use RuntimeException;

final class RazorpayPayment implements PaymentInterface
{
    public function createOrder(Orders $order): mixed
    {
        if (! class_exists(Api::class)) {
            throw new RuntimeException('Razorpay SDK is not installed.');
        }

        $api = new Api(
            (string) config('services.razorpay.key'),
            (string) config('services.razorpay.secret')
        );

        return $api->order->create([
            'receipt' => (string) $order->id,
            'amount' => (int) round((float) $order->total_amount * 100),
            'currency' => 'INR',
        ]);
    }

    public function createOrderFromData(array $orderData): mixed
    {
        if (! class_exists(Api::class)) {
            throw new RuntimeException('Razorpay SDK is not installed.');
        }

        $api = new Api(
            (string) config('services.razorpay.key'),
            (string) config('services.razorpay.secret')
        );

        // Calculate total amount from order data
        $totals = app(CalculateOrderTotals::class)->execute($orderData);

        return $api->order->create([
            'receipt' => 'temp_'.time().'_'.$orderData['customer_id'], // Temporary receipt
            'amount' => (int) round((float) $totals['total_amount'] * 100),
            'currency' => 'INR',
        ]);
    }

    public function verifyPayment(array $data): bool
    {
        if (! class_exists(Api::class)) {
            throw new RuntimeException('Razorpay SDK is not installed.');
        }

        $api = new Api(
            (string) config('services.razorpay.key'),
            (string) config('services.razorpay.secret')
        );

        $api->utility->verifyPaymentSignature($data);

        return true;
    }
}
