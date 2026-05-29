<?php

declare(strict_types=1);

namespace App\Services\Payments\Gateways;

use App\Actions\Order\CalculateOrderTotals;
use App\Models\Orders;
use App\Services\Payments\Contracts\PaymentInterface;
use Illuminate\Support\Str;

final class PhonePePayment implements PaymentInterface
{
    public function createOrder(Orders $order): mixed
    {
        return [
            'merchantId' => (string) config('services.phonepe.merchant_id'),
            'merchantTransactionId' => 'ord_'.Str::uuid()->toString(),
            'merchantUserId' => (string) $order->customer_id,
            'amount' => (int) round((float) $order->total_amount * 100),
            'redirectUrl' => (string) config('services.phonepe.callback_url', url('/api/v1/customer/payment/callback')),
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];
    }

    public function createOrderFromData(array $orderData): mixed
    {
        $totals = app(CalculateOrderTotals::class)->execute($orderData);

        return [
            'merchantId' => (string) config('services.phonepe.merchant_id'),
            'merchantTransactionId' => 'ord_'.Str::uuid()->toString(),
            'merchantUserId' => (string) $orderData['customer_id'],
            'amount' => (int) round((float) $totals['total_amount'] * 100),
            'redirectUrl' => (string) config('services.phonepe.callback_url', url('/api/v1/customer/payment/callback')),
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];
    }

    public function verifyPayment(array $data): bool
    {
        return isset($data['merchantTransactionId']) && isset($data['status']);
    }
}
