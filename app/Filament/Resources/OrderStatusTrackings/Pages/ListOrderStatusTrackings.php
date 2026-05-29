<?php

namespace App\Filament\Resources\OrderStatusTrackings\Pages;

use App\Filament\Resources\OrderStatusTrackings\OrderStatusTrackingResource;
use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\OrderStatusTracking;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;

class ListOrderStatusTrackings extends ListRecords
{
    protected static string $resource = OrderStatusTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            
            Action::make('quick_status_update')
                ->label('Quick Status Update')
                ->icon('heroicon-o-bolt')
                ->color('info')
                ->form([
                    Grid::make(2)
                        ->schema([
                            Select::make('order_id')
                                ->label('Select Order')
                                ->options(Orders::with('customer')->get()->mapWithKeys(function ($order) {
                                    return [$order->id => "{$order->order_number} - {$order->customer->first_name} {$order->customer->last_name}"];
                                }))
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($set, $state) {
                                    // Reset the new status when order changes
                                    $set('new_status_id', null);
                                }),

                            Select::make('new_status_id')
                                ->label('New Status')
                                ->options(function ($get) {
                                    $orderId = $get('order_id');
                                    if (!$orderId) return [];
                                    
                                    $order = Orders::find($orderId);
                                    if (!$order) return [];
                                    
                                    $currentStatus = OrderStatuses::where('code', $order->current_status_code)->first();
                                    if (!$currentStatus) return [];
                                    
                                    return OrderStatuses::where('sequence', '>', $currentStatus->sequence)
                                        ->where('status', 1)
                                        ->orderBy('sequence')
                                        ->pluck('name', 'id');
                                })
                                ->live()
                                ->required(),
                        ]),
                    
                    Textarea::make('description')
                        ->label('Update Description')
                        ->placeholder('Enter details about this status update...')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $order = Orders::find($data['order_id']);
                    $newStatus = OrderStatuses::find($data['new_status_id']);
                    
                    if ($order && $newStatus) {
                        // Create new tracking record
                        OrderStatusTracking::create([
                            'order_id' => $order->id,
                            'customer_id' => $order->customer_id,
                            'driver_id' => $order->driver_id,
                            'order_status_id' => $newStatus->id,
                            'status_code' => $newStatus->code,
                            'status_name' => $newStatus->name,
                            'description' => $data['description'] ?? "Status updated to {$newStatus->name}",
                            'status' => 1,
                        ]);
                        
                        // Update order current status
                        $order->update([
                            'current_status_id' => $newStatus->id,
                            'current_status_code' => $newStatus->code,
                        ]);
                        
                        // Update special timestamps
                        if ($newStatus->code === 'confirmed') {
                            $order->update(['confirmed_at' => now()]);
                        } elseif ($newStatus->code === 'delivered') {
                            $order->update(['delivered_at' => now()]);
                        } elseif ($newStatus->code === 'cancelled') {
                            $order->update(['cancelled_at' => now(), 'is_cancelled' => true]);
                        }
                    }
                })
                ->modalHeading('Quick Status Update')
                ->modalDescription('Update order status quickly without creating a full tracking record form')
                ->modalSubmitActionLabel('Update Status')
                ->successNotificationTitle('Status Updated Successfully'),
        ];
    }
}