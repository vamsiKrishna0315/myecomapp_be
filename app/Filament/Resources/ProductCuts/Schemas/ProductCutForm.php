<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductCuts\Schemas;

use App\Enums\WeightUnit;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class ProductCutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                TextInput::make('cut_name')
                    ->required(),
                TextInput::make('cut_code')
                    ->required(),
                TextInput::make('price_per_kg')
                    ->required()
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $minimumWeight = (float) ($get('minimum_weight'));
                        $pricePerKg = (float) $state;
                        $pricePerPiece = $pricePerKg * $minimumWeight;
                        $set('price_per_piece', $pricePerPiece);
                    }),
                TextInput::make('minimum_weight')
                    ->required()
                    ->numeric()
                    ->default(0.125)
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $pricePerKg = (float) ($get('price_per_kg'));
                        $minimumWeight = (float) $state;
                        $pricePerPiece = $pricePerKg * $minimumWeight;
                        $set('price_per_piece', $pricePerPiece);
                    }),
                TextInput::make('price_per_piece')
                    ->numeric()
                    ->disabled(),
                Select::make('weight_unit')
                    ->options(WeightUnit::class)
                    ->required()
                    ->default(WeightUnit::KILOGRAMS->value),
                TextInput::make('net_weight')
                    ->numeric(),
                Select::make('cut_type_id')
                    ->relationship('cutType', 'name'),
                TextInput::make('preparation_style'),
                Select::make('product_grade_id')
                    ->relationship('productGrade', 'name'),
                Toggle::make('is_cleaned')
                    ->required(),
                Toggle::make('is_skinless')
                    ->required(),
                TextInput::make('stock_quantity')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('stock_unit')
                    ->required()
                    ->default('kg'),
                TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(1),
                Toggle::make('show_live')
                    ->required()
                    ->default(false),
                Toggle::make('requires_advance_order')
                    ->required(),
                TextInput::make('preparation_time')
                    ->numeric(),
                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('product-cuts'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('popular')
                    ->required(),
                TextInput::make('display_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('protein_per_100g')
                    ->numeric(),
                TextInput::make('fat_per_100g')
                    ->numeric(),
                TextInput::make('calories_per_100g')
                    ->numeric(),
            ]);
    }
}
