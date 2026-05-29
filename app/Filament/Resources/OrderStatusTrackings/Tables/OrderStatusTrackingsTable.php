<?php

namespace App\Filament\Resources\OrderStatusTrackings\Tables;

use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\OrderStatusTracking;
use App\Models\Customer;
use App\Models\Driver;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;

class OrderStatusTrackingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('order.order_number')
                    ->label('Order Number')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->tooltip('Click to copy'),

                TextColumn::make('customer')
                    ->label('Customer')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->customer ? $record->customer->first_name . ' ' . $record->customer->last_name : 'N/A'),

                BadgeColumn::make('status_name')
                    ->label('Status')
                    ->colors([
                        'success' => ['delivered', 'confirmed'],
                        'warning' => ['pending', 'processing', 'ready_for_pickup'],
                        'info' => ['assigned_to_driver', 'driver_accepted', 'driver_at_store', 'driver_picked_up'],
                        'primary' => ['driver_nearby', 'driver_reached'],
                        'danger' => ['cancelled', 'failed'],
                        'secondary' => ['returned'],
                    ])
                    ->sortable(),

                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->sortable()
                    ->placeholder('Not assigned'),
                
                TextColumn::make('storeVendor.name')
                    ->label('Store Vendor')
                    ->sortable()
                    ->searchable()
                    ->placeholder('N/A')
                    ->toggleable(),

                TextColumn::make('lat')
                    ->label('Latitude')
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('lng')
                    ->label('Longitude')
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        
                        return $state;
                    })
                    ->toggleable(),

                BooleanColumn::make('status')
                    ->label('Active')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('order_id')
                    ->label('Order')
                    ->options(Orders::pluck('order_number', 'id'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status_code')
                    ->label('Status')
                    ->options(OrderStatuses::pluck('name', 'code'))
                    ->searchable(),

                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->options(Customer::all()->mapWithKeys(function ($customer) {
                        return [$customer->id => $customer->first_name . ' ' . $customer->last_name];
                    }))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('driver_id')
                    ->label('Driver')
                    ->options(Driver::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Filter::make('has_location')
                    ->label('Has Location Data')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('lat')->whereNotNull('lng')),

                Filter::make('active')
                    ->label('Active Only')
                    ->query(fn (Builder $query): Builder => $query->where('status', 1))
                    ->default(),
            ])
            ->recordActions([
                EditAction::make(),
                
                Action::make('next_status')
                    ->label('Next Status')
                    ->icon('heroicon-o-arrow-right')
                    ->color('success')
                    ->visible(function ($record) {
                        // Get current order and check if it can be progressed
                        $order = $record->order;
                        $currentStatus = OrderStatuses::where('code', $order->current_status_code)->first();
                        
                        // Show button only if current status is not final
                        return $currentStatus && !$currentStatus->is_final;
                    })
                    ->action(function ($record) {
                        $order = $record->order;
                        $currentStatus = OrderStatuses::where('code', $order->current_status_code)->first();
                        
                        // Get next status in sequence
                        $nextStatus = OrderStatuses::where('sequence', '>', $currentStatus->sequence)
                            ->where('status', 1)
                            ->orderBy('sequence')
                            ->first();
                        
                        if ($nextStatus) {
                            // Create new tracking record
                            OrderStatusTracking::create([
                                'order_id' => $order->id,
                                'customer_id' => $order->customer_id,
                                'driver_id' => $order->driver_id,
                                'order_status_id' => $nextStatus->id,
                                'status_code' => $nextStatus->code,
                                'status_name' => $nextStatus->name,
                                'description' => "Status updated to {$nextStatus->name}",
                                'status' => 1,
                            ]);
                            
                            // Update order current status
                            $order->update([
                                'current_status_id' => $nextStatus->id,
                                'current_status_code' => $nextStatus->code,
                            ]);
                            
                            // Update timestamps for special statuses
                            if ($nextStatus->code === 'confirmed') {
                                $order->update(['confirmed_at' => now()]);
                            } elseif ($nextStatus->code === 'delivered') {
                                $order->update(['delivered_at' => now()]);
                            }
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Update Order Status')
                    ->modalDescription(function ($record) {
                        $order = $record->order;
                        $currentStatus = OrderStatuses::where('code', $order->current_status_code)->first();
                        $nextStatus = OrderStatuses::where('sequence', '>', $currentStatus->sequence)
                            ->where('status', 1)
                            ->orderBy('sequence')
                            ->first();
                        
                        return $nextStatus ? "Are you sure you want to update the status to '{$nextStatus->name}'?" : '';
                    }),
                
                Action::make('view_location')
                    ->label('View Location')
                    ->icon('heroicon-o-map-pin')
                    ->color('info')
                    ->visible(fn ($record) => $record->lat && $record->lng)
                    ->url(fn ($record) => "https://www.google.com/maps?q={$record->lat},{$record->lng}")
                    ->openUrlInNewTab(),

                Action::make('track_package')
                    ->label('Track Package')
                    ->icon('heroicon-o-clock')
                    ->color('primary')
                    ->url(fn ($record) => url("/admin/order-status-trackings/{$record->id}/timeline"))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
