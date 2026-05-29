<?php

declare(strict_types=1);

namespace App\Services\Payments\Gateways;

use App\Actions\Order\CalculateOrderTotals;
use App\Models\Orders;
use App\Services\Payments\Contracts\PaymentInterface;
use JsonException;

final class PaytmPayment implements PaymentInterface
{
    public function createOrder(Orders $order): mixed
    {
        $params = [
            'MID' => (string) config('services.paytm.mid'),
            'ORDER_ID' => (string) $order->id,
            'TXN_AMOUNT' => (string) $order->total_amount,
            'CUST_ID' => (string) $order->customer_id,
            'WEBSITE' => (string) config('services.paytm.website'),
            'CALLBACK_URL' => (string) config('services.paytm.callback_url', url('/api/v1/customer/payment/callback')),
        ];

        $params['CHECKSUMHASH'] = $this->generateSignature($params);

        return $params;
    }

    public function createOrderFromData(array $orderData): mixed
    {
        $totals = app(CalculateOrderTotals::class)->execute($orderData);

        $params = [
            'MID' => (string) config('services.paytm.mid'),
            'ORDER_ID' => 'temp_'.time().'_'.$orderData['customer_id'], // Temporary order ID
            'TXN_AMOUNT' => (string) $totals['total_amount'],
            'CUST_ID' => (string) $orderData['customer_id'],
            'WEBSITE' => (string) config('services.paytm.website'),
            'CALLBACK_URL' => (string) config('services.paytm.callback_url', url('/api/v1/customer/payment/callback')),
        ];

        $params['CHECKSUMHASH'] = $this->generateSignature($params);

        return $params;
    }

    public function verifyPayment(array $data): bool
    {
        return isset($data['ORDERID']) && isset($data['STATUS']);
    }

    private function generateSignature(array $params): string
    {
        if (class_exists('PaytmChecksum')) {
            return (string) call_user_func(['PaytmChecksum', 'generateSignature'], $params, (string) config('services.paytm.key'));
        }

        try {
            return hash_hmac('sha256', json_encode($params, JSON_THROW_ON_ERROR), (string) config('services.paytm.key'));
        } catch (JsonException) {
            return hash_hmac('sha256', serialize($params), (string) config('services.paytm.key'));
        }
    }
}
