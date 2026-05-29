<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\OrderStatusTracking;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

final class OrderTrackingPage extends Page implements HasForms
{
    use InteractsWithForms;

    public ?int $orderId = null;

    public $orderData = null;

    public $trackingEvents = [];

    public $allStatuses = [];

    protected string $view = 'filament.pages.order-tracking';

    public static function getNavigationLabel(): string
    {
        return 'Track Orders';
    }

    public function getTitle(): string
    {
        return $this->orderData
            ? "Track Order #{$this->orderData->order_number}"
            : 'Order Tracking';
    }

    public function mount(): void
    {
        $this->form->fill();
        $this->loadAllStatuses();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('orderId')
                    ->label('Select Order')
                    ->placeholder('Choose an order to track')
                    ->options(function () {
                        return Orders::with('customer')
                            ->latest()
                            ->limit(100)
                            ->get()
                            ->mapWithKeys(function ($order) {
                                $customerName = $order->customer ? $order->customer->full_name : 'Unknown';

                                return [$order->id => "#{$order->order_number} - {$customerName}"];
                            });
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->loadOrderTracking($state)),
            ])
            ->statePath('form');
    }

    public function loadAllStatuses(): void
    {
        $this->allStatuses = OrderStatuses::orderBy('sequence')
            ->orOrderBy('display_order')
            ->orOrderBy('id')
            ->get()
            ->toArray();
    }

    public function loadOrderTracking(?int $orderId): void
    {
        if (! $orderId) {
            $this->orderData = null;
            $this->trackingEvents = [];

            return;
        }

        // Load order data
        $this->orderData = Orders::with(['customer', 'currentStatus'])
            ->find($orderId);

        if (! $this->orderData) {
            return;
        }

        // Load tracking events
        $trackingEvents = OrderStatusTracking::where('order_id', $orderId)
            ->with(['orderStatus', 'driver', 'storeVendor'])
            ->orderBy('created_at')
            ->get();

        // Create timeline events
        $events = collect();

        // Add order creation event
        $events->push([
            'id' => 'order_created',
            'type' => 'order_created',
            'title' => 'Order Created',
            'description' => 'Order was successfully placed',
            'timestamp' => $this->orderData->created_at,
            'status' => 'completed',
            'icon' => 'heroicon-o-shopping-cart',
            'color' => 'success',
            'details' => [
                'Order Number' => $this->orderData->order_number,
                'Customer' => $this->orderData->customer?->full_name ?? 'Unknown',
                'Total Amount' => '₹'.number_format($this->orderData->total_amount, 2),
            ],
        ]);

        // Add confirmed event if exists
        if ($this->orderData->confirmed_at) {
            $events->push([
                'id' => 'order_confirmed',
                'type' => 'order_confirmed',
                'title' => 'Order Confirmed',
                'description' => 'Order has been confirmed and is being prepared',
                'timestamp' => $this->orderData->confirmed_at,
                'status' => 'completed',
                'icon' => 'heroicon-o-check-circle',
                'color' => 'success',
                'details' => [],
            ]);
        }

        // Add tracking events
        foreach ($trackingEvents as $event) {
            $events->push([
                'id' => 'tracking_'.$event->id,
                'type' => 'status_tracking',
                'title' => $event->status_name,
                'description' => $event->description ?: $event->status_name,
                'timestamp' => $event->created_at,
                'status' => 'completed',
                'icon' => $this->getStatusIcon($event->status_code),
                'color' => $this->getStatusColor($event->status_code),
                'details' => array_filter([
                    'Status Code' => $event->status_code,
                    'Driver' => $event->driver?->name,
                    'Store Vendor' => $event->storeVendor?->name,
                    'Location' => $event->lat && $event->lng ? "Lat: {$event->lat}, Lng: {$event->lng}" : null,
                ]),
            ]);
        }

        // Add delivered event if exists
        if ($this->orderData->delivered_at) {
            $events->push([
                'id' => 'order_delivered',
                'type' => 'order_delivered',
                'title' => 'Order Delivered',
                'description' => 'Order has been successfully delivered',
                'timestamp' => $this->orderData->delivered_at,
                'status' => 'completed',
                'icon' => 'heroicon-o-truck',
                'color' => 'success',
                'details' => [],
            ]);
        }

        // Add pending/future events based on order status and predefined statuses
        $currentStatusCodes = $trackingEvents->pluck('status_code')->toArray();
        $futureStatuses = $this->getFutureStatuses($currentStatusCodes);

        foreach ($futureStatuses as $status) {
            $events->push([
                'id' => 'future_'.$status['code'],
                'type' => 'future_status',
                'title' => $status['name'],
                'description' => $status['description'] ?: 'Pending status',
                'timestamp' => null,
                'status' => 'pending',
                'icon' => $this->getStatusIcon($status['code']),
                'color' => 'warning',
                'details' => [],
            ]);
        }

        $this->trackingEvents = $events->sortBy('timestamp')->values()->toArray();
    }

    private function getFutureStatuses(array $completedCodes): array
    {
        $allCodes = collect($this->allStatuses)->pluck('code')->toArray();
        $pendingCodes = array_diff($allCodes, $completedCodes);

        return collect($this->allStatuses)
            ->whereIn('code', $pendingCodes)
            ->where('is_final', '!=', true)
            ->take(3) // Limit future events
            ->toArray();
    }

    private function getStatusIcon(string $statusCode): string
    {
        return match ($statusCode) {
            'pending' => 'heroicon-o-clock',
            'confirmed' => 'heroicon-o-check-circle',
            'preparing' => 'heroicon-o-cog-6-tooth',
            'ready' => 'heroicon-o-check-badge',
            'picked_up' => 'heroicon-o-truck',
            'in_transit' => 'heroicon-o-map',
            'delivered' => 'heroicon-o-check',
            'cancelled' => 'heroicon-o-x-circle',
            default => 'heroicon-o-information-circle',
        };
    }

    private function getStatusColor(string $statusCode): string
    {
        return match ($statusCode) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'preparing' => 'primary',
            'ready' => 'success',
            'picked_up' => 'info',
            'in_transit' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }
}
