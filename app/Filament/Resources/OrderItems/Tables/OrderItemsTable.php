<?php

namespace App\Filament\Resources\OrderItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cut_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product_name')
                    ->searchable(),
                TextColumn::make('cut_name')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('price_per_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_per_piece')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ordered_weight')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('actual_weight')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight_unit')
                    ->searchable(),
                TextColumn::make('line_subtotal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('line_discount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('line_tax')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('line_total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('preparation_style')
                    ->searchable(),
                IconColumn::make('is_cleaned')
                    ->boolean(),
                IconColumn::make('is_skinless')
                    ->boolean(),
                TextColumn::make('order_item_status')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
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
