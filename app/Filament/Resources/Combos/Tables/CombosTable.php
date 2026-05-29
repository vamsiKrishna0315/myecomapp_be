<?php

declare(strict_types=1);

namespace App\Filament\Resources\Combos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class CombosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (mb_strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('total_price')
                    ->money('INR')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->formatStateUsing(fn (int|string|null $state): string => (int) $state === 1 ? 'Active' : 'Inactive')
                    ->colors([
                        'success' => fn (int|string|null $state): bool => (int) $state === 1,
                        'danger' => fn (int|string|null $state): bool => (int) $state === 0,
                    ]),
                BooleanColumn::make('is_visible'),
                TextColumn::make('display_order')
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
