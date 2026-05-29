<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Orders;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UpdateOrderStatusEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Orders $order;

    public string $nextStatusCode;

    public array $additionalData;

    public function __construct(Orders $order, string $nextStatusCode, array $additionalData = [])
    {
        $this->order = $order;
        $this->nextStatusCode = $nextStatusCode;
        $this->additionalData = $additionalData;
    }
}
