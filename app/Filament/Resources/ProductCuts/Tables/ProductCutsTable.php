<?php

namespace App\Filament\Resources\ProductCuts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductCutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('cut_name')
                    ->searchable(),
                TextColumn::make('cut_code')
                    ->searchable(),
                TextColumn::make('price_per_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_per_piece')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('minimum_weight')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight_unit')
                    ->searchable(),
                TextColumn::make('net_weight')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cutType.name')
                    ->searchable(),
                TextColumn::make('preparation_style')
                    ->searchable(),
                TextColumn::make('productGrade.name')
                    ->searchable(),
                IconColumn::make('is_cleaned')
                    ->boolean(),
                IconColumn::make('is_skinless')
                    ->boolean(),
                TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_unit')
                    ->searchable(),
                TextColumn::make('status')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('show_live')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('requires_advance_order')
                    ->boolean(),
                TextColumn::make('preparation_time')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('image'),
                IconColumn::make('popular')
                    ->boolean(),
                TextColumn::make('display_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('protein_per_100g')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fat_per_100g')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('calories_per_100g')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
