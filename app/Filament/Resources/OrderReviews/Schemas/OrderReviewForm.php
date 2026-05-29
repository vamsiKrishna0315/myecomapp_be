<?php
namespace App\Filament\Resources\OrderReviews\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;

class OrderReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => ($record->order_number ?? "#{$record->id}"))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Order')
                    ->reactive()
                    ->afterStateUpdated(function ($set, $get, $state) {
                        if ($state) {
                            $order = \App\Models\Orders::find($state);
                            if ($order) {
                                $set('customer_id', $order->customer_id);
                                
                                // Also update the customer name display
                                $customer = \App\Models\Customer::find($order->customer_id);
                                $set('customer_name', $customer ? $customer->full_name : '');
                            }
                        } else {
                            $set('customer_id', null);
                            $set('customer_name', '');
                        }
                    }),
                    
                // Hidden field to store the customer_id (will be auto-filled from order)
                TextInput::make('customer_id')
                    ->hidden()
                    ->dehydrated()
                    ->required(),
                    
                // Read-only display of the customer name as a disabled TextInput
                TextInput::make('customer_name')
                    ->label('Customer')
                    ->disabled()
                    ->dehydrated(false)
                    ->default('')
                    ->columnSpan(1),
                    
                TextInput::make('driver_id')
                    ->numeric(),
                    
                TextInput::make('product_rating')
                    ->numeric(),
                    
                TextInput::make('delivery_rating')
                    ->numeric(),
                    
                TextInput::make('overall_rating')
                    ->required()
                    ->numeric(),
                    
                Textarea::make('review')
                    ->columnSpanFull(),
                    
                TextInput::make('images'),
                
                Toggle::make('is_verified_purchase')
                    ->required(),
                    
                TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}