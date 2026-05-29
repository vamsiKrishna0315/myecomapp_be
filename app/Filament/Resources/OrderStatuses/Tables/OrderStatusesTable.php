<?php

namespace App\Filament\Resources\OrderStatuses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class OrderStatusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('name')
                    ->label('Status Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                ColorColumn::make('color')
                    ->label('Color')
                    ->copyable(),

                TextColumn::make('sequence')
                    ->label('Sequence')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_final')
                    ->label('Final')
                    ->boolean()
                    ->alignCenter()
                    ->tooltip('Cannot be changed once reached'),

                IconColumn::make('is_cancellable')
                    ->label('Cancellable')
                    ->boolean()
                    ->alignCenter()
                    ->tooltip('Orders can be cancelled from this status'),

                TextColumn::make('display_order')
                    ->label('Display Order')
                    ->sortable()
                    ->alignCenter(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (int $state): string => $state ? 'Active' : 'Inactive')
                    ->colors([
                        'success' => 1,
                        'danger' => 0,
                    ])
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),

                TernaryFilter::make('is_final')
                    ->label('Final Status')
                    ->placeholder('All statuses')
                    ->trueLabel('Final only')
                    ->falseLabel('Non-final only'),

                TernaryFilter::make('is_cancellable')
                    ->label('Cancellable')
                    ->placeholder('All statuses')
                    ->trueLabel('Cancellable only')
                    ->falseLabel('Non-cancellable only'),
            ])
            ->recordActions([
                EditAction::make()
                    ->tooltip('Edit status'),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('New Status')
                    ->icon('heroicon-o-plus'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sequence')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
