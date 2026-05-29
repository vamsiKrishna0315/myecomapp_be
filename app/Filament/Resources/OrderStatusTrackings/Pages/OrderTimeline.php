<?php

namespace App\Filament\Resources\OrderStatusTrackings\Pages;

use App\Filament\Resources\OrderStatusTrackings\OrderStatusTrackingResource;
use App\Models\OrderStatusTracking;
use App\Models\OrderStatuses;
use Filament\Resources\Pages\Page;

class OrderTimeline extends Page
{
    protected static string $resource = OrderStatusTrackingResource::class;

    protected string $view = 'filament.resources.order-status-trackings.timeline';

    public $order;
    public $events = [];

    public function mount($record): void
    {
        $tracking = OrderStatusTracking::find($record);
        if (!$tracking) {
            abort(404);
        }

        $order = $tracking->order;
        $this->order = $order;

        // Start with order created event
        $events = [];
        $events[] = [
            'key' => 'order_created',
            'label' => 'Order Created',
            'description' => "Order placed ({$order->order_number})",
            'timestamp' => $order->created_at,
            'status' => 'completed',
        ];

        // Load statuses in sequence and map tracking records
        $statuses = OrderStatuses::where('status', 1)->orderBy('sequence')->get();

        foreach ($statuses as $status) {
            $found = $order->statusTracking()->where('status_code', $status->code)->orderBy('created_at')->first();

            if ($found) {
                $events[] = [
                    'key' => $status->code,
                    'label' => $status->name,
                    'description' => $found->description ?? $status->description ?? $status->name,
                    'timestamp' => $found->created_at,
                    'status' => 'completed',
                ];
            } else {
                $events[] = [
                    'key' => $status->code,
                    'label' => $status->name,
                    'description' => $status->description ?? null,
                    'timestamp' => null,
                    'status' => 'pending',
                ];
            }
        }

        $this->events = $events;
    }

    // using default Page::route() behavior
}
