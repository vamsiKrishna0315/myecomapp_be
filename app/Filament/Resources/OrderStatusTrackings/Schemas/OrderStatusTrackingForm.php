<?php

namespace App\Filament\Resources\OrderStatusTrackings\Schemas;

use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\User;
use App\Models\StoreVendorOrders;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class OrderStatusTrackingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Information')
                    ->description('Select order and basic tracking details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('order_id')
                                    ->label('Order')
                                    ->options(Orders::with('customer')->get()->pluck('order_number', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $state) {
                                        if ($state) {
                                            $order = Orders::with('customer')->find($state);
                                            if ($order) {
                                                $set('customer_id', $order->customer_id);
                                                $set('driver_id', $order->driver_id);
                                                $set('status_code', $order->current_status_code);
                                                
                                                // Get the status name from order_statuses table
                                                $status = OrderStatuses::where('code', $order->current_status_code)->first();
                                                if ($status) {
                                                    $set('status_name', $status->name);
                                                    $set('order_status_id', $status->id);
                                                }
                                                
                                                // Auto-populate store_vendor_id from StoreVendorOrders
                                                $storeVendorOrder = StoreVendorOrders::where('order_id', $state)->first();
                                                if ($storeVendorOrder) {
                                                    $set('store_vendor_id', $storeVendorOrder->store_vendor_id);
                                                }
                                            }
                                        }
                                    }),

                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->options(Customer::all()->mapWithKeys(function ($customer) {
                                        return [$customer->id => $customer->first_name . ' ' . $customer->last_name];
                                    }))
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ]),

                Section::make('Status Information')
                    ->description('Current and next status options')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('order_status_id')
                                    ->label('Order Status')
                                    ->options(OrderStatuses::where('status', 1)->orderBy('sequence')->pluck('name', 'id'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $state) {
                                        if ($state) {
                                            $status = OrderStatuses::find($state);
                                            if ($status) {
                                                $set('status_code', $status->code);
                                                $set('status_name', $status->name);
                                            }
                                        }
                                    }),

                                TextInput::make('status_code')
                                    ->label('Status Code')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('status_name')
                                    ->label('Status Name')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ]),

                Section::make('Driver & Location')
                    ->description('Driver assignment, vendor tracking, and location data')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Select::make('driver_id')
                                    ->label('Driver')
                                    ->options(Driver::where('status', 1)->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload(),
                                
                                Select::make('store_vendor_id')
                                    ->label('Store Vendor')
                                    ->options(User::where('user_role', 'store_vendor')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Auto-populated from order (for reputation tracking)'),

                                TextInput::make('lat')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->placeholder('12.9716')
                                    ->helperText('GPS coordinate for location tracking'),

                                TextInput::make('lng')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->placeholder('77.5946')
                                    ->helperText('GPS coordinate for location tracking'),
                            ]),
                    ]),

                Section::make('Additional Information')
                    ->description('Description and status control')
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Additional details about this status update...')
                            ->columnSpanFull()
                            ->rows(3),

                        Toggle::make('status')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Toggle to activate/deactivate this tracking record'),
                    ]),
            ]);
    }
}
