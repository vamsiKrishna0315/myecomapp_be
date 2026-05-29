<?php

declare(strict_types=1);

namespace App\Services\Payments\Gateways;

use App\Actions\Order\CalculateOrderTotals;
use App\Models\Orders;
use App\Services\Payments\Contracts\PaymentInterface;

final class CodPayment implements PaymentInterface
{
    public function createOrder(Orders $order): mixed
    {
        // For COD, no payment gateway order is needed
        // Return minimal data or null
        return [
            'id' => 'cod_'.$order->id,
            'amount' => $order->total_amount,
            'currency' => 'INR',
            'status' => 'cod_pending',
        ];
    }

    public function createOrderFromData(array $orderData): mixed
    {
        $totals = app(CalculateOrderTotals::class)->execute($orderData);

        return [
            'id' => 'cod_temp_'.time().'_'.$orderData['customer_id'],
            'amount' => $totals['total_amount'],
            'currency' => 'INR',
            'status' => 'cod_pending',
        ];
    }

    public function verifyPayment(array $data): bool
    {
        // For COD, payment verification happens when order is delivered
        // This method might not be used for COD, but implemented for interface
        return true;
    }
}
