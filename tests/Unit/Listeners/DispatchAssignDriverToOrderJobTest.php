<?php

declare(strict_types=1);

use App\Events\OrderCreated;
use App\Jobs\AssignDriverToOrderJob;
use App\Listeners\DispatchAssignDriverToOrderJob;
use App\Models\Orders;
use Illuminate\Support\Facades\Queue;

it('dispatches assign driver job when order is created event is handled', function (): void {
    Queue::fake();

    $order = new Orders();
    $order->id = 123;

    $listener = new DispatchAssignDriverToOrderJob();
    $listener->handle(new OrderCreated($order));

    Queue::assertPushed(AssignDriverToOrderJob::class, function (AssignDriverToOrderJob $job): bool {
        return $job->orderId === 123;
    });
});
