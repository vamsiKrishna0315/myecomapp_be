<?php

namespace App\Filament\Resources\OrderItems\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use App\Models\Orders;
use App\Models\Product;
use App\Models\ProductCut;
use App\Models\Category;

class OrderItemsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Item Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('order_id')
                                    ->relationship('order', 'order_number')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->label('Order')
                                    ->columnSpan(1),

                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get, $state) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('product_name', $product->name);
                                                $set('sku', $product->sku);
                                                $set('category_id', $product->category_id);
                                            }
                                        }
                                        // Reset cut selection when product changes
                                        $set('cut_id', null);
                                        $set('cut_name', null);
                                        $set('price_per_kg', 0);
                                        $set('price_per_piece', 0);
                                    })
                                    ->label('Product')
                                    ->columnSpan(1),

                                Select::make('cut_id')
                                    ->options(function ($get) {
                                        $productId = $get('product_id');
                                        if (!$productId) {
                                            return [];
                                        }
                                        return ProductCut::where('product_id', $productId)
                                            ->where('status', 1)
                                            ->get()
                                            ->pluck('cut_name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get, $state) {
                                        if ($state) {
                                            $cut = ProductCut::find($state);
                                            if ($cut) {
                                                $set('cut_name', $cut->cut_name);
                                                $set('price_per_kg', $cut->price_per_kg);
                                                $set('price_per_piece', $cut->price_per_piece);
                                                $set('weight_unit', $cut->weight_unit);
                                                $set('preparation_style', $cut->preparation_style);
                                                $set('is_cleaned', $cut->is_cleaned);
                                                $set('is_skinless', $cut->is_skinless);
                                            }
                                        }
                                    })
                                    ->label('Cut Type')
                                    ->placeholder('Select product first')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('product_name')
                                    ->label('Product Name')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),

                                TextInput::make('cut_name')
                                    ->label('Cut Name')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),

                                Select::make('category_id')
                                    ->relationship('product.category', 'category_name')
                                    ->disabled()
                                    ->dehydrated()
                                    ->label('Category')
                                    ->columnSpan(1),

                                TextInput::make('preparation_style')
                                    ->label('Preparation Style')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Weight & Pricing')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('ordered_weight')
                                    ->numeric()
                                    ->step(0.1)
                                    ->minValue(0)
                                    ->required()
                                    ->label('Ordered Weight')
                                    ->suffix('kg')
                                    ->columnSpan(1),

                                TextInput::make('actual_weight')
                                    ->numeric()
                                    ->step(0.1)
                                    ->minValue(0)
                                    ->label('Actual Weight')
                                    ->suffix('kg')
                                    ->placeholder('Leave empty to use ordered weight')
                                    ->columnSpan(1),

                                TextInput::make('weight_unit')
                                    ->label('Weight Unit')
                                    ->default('kg')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),

                                TextInput::make('price_per_kg')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₹')
                                    ->disabled()
                                    ->dehydrated()
                                    ->label('Price per Kg')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('price_per_piece')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₹')
                                    ->disabled()
                                    ->dehydrated()
                                    ->label('Price per Piece')
                                    ->columnSpan(1),

                                Toggle::make('is_cleaned')
                                    ->label('Cleaned')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),

                                Toggle::make('is_skinless')
                                    ->label('Skinless')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Line Totals')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('line_subtotal')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₹')
                                    ->required()
                                    ->label('Line Subtotal')
                                    ->columnSpan(1),

                                TextInput::make('line_discount')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₹')
                                    ->default(0)
                                    ->label('Line Discount')
                                    ->columnSpan(1),

                                TextInput::make('line_tax')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₹')
                                    ->default(0)
                                    ->label('Line Tax')
                                    ->columnSpan(1),

                                TextInput::make('line_total')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₹')
                                    ->required()
                                    ->label('Line Total')
                                    ->extraAttributes(['class' => 'font-bold'])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Status & Instructions')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('order_item_status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'confirmed' => 'Confirmed',
                                        'preparing' => 'Preparing',
                                        'ready' => 'Ready',
                                        'delivered' => 'Delivered',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->label('Item Status')
                                    ->columnSpan(1),

                                Select::make('status')
                                    ->options([
                                        1 => 'Active',
                                        0 => 'Inactive',
                                    ])
                                    ->default(1)
                                    ->required()
                                    ->label('Record Status')
                                    ->columnSpan(1),
                            ]),

                        Textarea::make('special_instructions')
                            ->maxLength(500)
                            ->label('Special Instructions')
                            ->placeholder('Any special preparation instructions for this item...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
