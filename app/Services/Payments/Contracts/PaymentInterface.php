<?php

declare(strict_types=1);

namespace App\Services\Payments\Contracts;

use App\Models\Orders;

interface PaymentInterface
{
    public function createOrder(Orders $order): mixed;

    public function createOrderFromData(array $orderData): mixed;

    public function verifyPayment(array $data): bool;
}
