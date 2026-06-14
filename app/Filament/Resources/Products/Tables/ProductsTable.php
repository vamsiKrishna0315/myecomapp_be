<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primary_image')
                    ->circular()
                    ->label('Image'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sku')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('category.category_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Short Description')
                    ->limit(50),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable()
                    ->color(fn ($record) => $record->stock_quantity <= $record->low_stock_threshold
                            ? 'danger'
                            : 'success'
                    ),

                TextColumn::make('price')
                    ->money('INR')
                    ->sortable(),
            ])
            ->defaultSort('name', 'asc')
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
