<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartItems\Schemas;

use App\Models\Customer;
use App\Models\ProductCut;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CartItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer & Product Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                                    ->searchable(['first_name', 'last_name', 'email'])
                                    ->preload()
                                    ->required(),

                                Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->searchable(['name', 'sku'])
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('product_cut_id', null)),
                            ]),

                        Select::make('product_cut_id')
                            ->label('Product Cut/Variant')
                            ->relationship('productCut', 'cut_name')
                            ->options(function (callable $get) {
                                $productId = $get('product_id');
                                if (! $productId) {
                                    return [];
                                }

                                return ProductCut::where('product_id', $productId)
                                    ->where('status', 1)
                                    ->pluck('cut_name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),

                Section::make('Quantity & Pricing')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0.001)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        $unitPrice = $get('unit_price');
                                        if ($unitPrice && $state) {
                                            $set('total_price', $state * $unitPrice);
                                        }
                                    }),

                                Select::make('quantity_unit')
                                    ->label('Unit')
                                    ->options([
                                        'kg' => 'Kilogram (kg)',
                                        'piece' => 'Piece',
                                        'gram' => 'Gram (g)',
                                    ])
                                    ->required()
                                    ->default('kg'),

                                TextInput::make('weight')
                                    ->label('Actual Weight (kg)')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->nullable(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('unit_price')
                                    ->label('Unit Price (₹)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        $quantity = $get('quantity');
                                        if ($quantity && $state) {
                                            $set('total_price', $quantity * $state);
                                        }
                                    }),

                                TextInput::make('total_price')
                                    ->label('Total Price (₹)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->required()
                                    ->readOnly(),
                            ]),
                    ]),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('special_instructions')
                            ->label('Special Instructions')
                            ->rows(3)
                            ->nullable(),

                        TextInput::make('session_id')
                            ->label('Session ID (for guest users)')
                            ->nullable(),

                        // Toggle::make('status')
                        //     ->label('Active')
                        //     ->default(true)
                        //     ->helperText('Inactive cart items are hidden from the customer'),
                    ]),
            ]);
    }
}
