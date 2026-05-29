<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\OrderCreated;
use App\Listeners\CreateOrderStatusTracking;
use App\Listeners\DispatchAssignDriverToOrderJob;
use App\Models\StoreVendorOrders;
use App\Observers\StoreVendorOrdersObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

final class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderCreated::class => [
            CreateOrderStatusTracking::class,
            DispatchAssignDriverToOrderJob::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        StoreVendorOrders::observe(StoreVendorOrdersObserver::class);
    }
}
