<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerFavoriteItems\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CustomerFavoriteItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Favorite Item Details')
                    ->description('Manage customer favorite products')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                                    ->searchable(['first_name', 'last_name', 'email'])
                                    ->preload()
                                    ->required()
                                    ->helperText('Select the customer who favorited this product'),

                                Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} (SKU: {$record->sku})")
                                    ->searchable(['name', 'sku'])
                                    ->preload()
                                    ->required()
                                    ->helperText('Select the product to add to favorites'),
                            ]),

                        // Toggle::make('status')
                        //     ->label('Active')
                        //     ->default(true)
                        //     ->helperText('Inactive favorites are hidden from the customer')
                        //     ->inline(false),
                    ])
                    ->columns(1),
            ]);
    }
}
