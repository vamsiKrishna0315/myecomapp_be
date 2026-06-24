<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class CartItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('productCut.cut_name')
                    ->label('Cut/Variant')
                    ->searchable()
                    ->sortable()
                    ->placeholder('No cut selected'),

                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->formatStateUsing(fn ($state, $record) => number_format($state, 3).' '.$record->quantity_unit)
                    ->sortable(),

                TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->formatStateUsing(fn ($state, $record) => '₹'.number_format($state, 2).'/'.$record->quantity_unit)
                    ->sortable(),

                TextColumn::make('total_price')
                    ->label('Total Price')
                    ->formatStateUsing(fn ($state) => '₹'.number_format($state, 2))
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive')
                    ->colors([
                        'success' => 1,
                        'danger' => 0,
                    ]),

                TextColumn::make('special_instructions')
                    ->label('Instructions')
                    ->limit(30)
                    ->placeholder('No instructions'),

                TextColumn::make('created_at')
                    ->label('Added At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'first_name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                // TernaryFilter::make('status')
                //     ->label('Status')
                //     ->placeholder('All items')
                //     ->trueLabel('Active only')
                //     ->falseLabel('Inactive only'),

                SelectFilter::make('quantity_unit')
                    ->label('Unit Type')
                    ->options([
                        'kg' => 'Kilogram',
                        'piece' => 'Piece',
                        'gram' => 'Gram',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
