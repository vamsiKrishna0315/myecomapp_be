<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\AssignDriverToOrderJob;

final class DispatchAssignDriverToOrderJob
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        AssignDriverToOrderJob::dispatch((int) $event->order->id);
    }
}
