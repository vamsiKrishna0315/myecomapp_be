<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('banner_name')
                    ->label('Banner Name')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('banner_path')
                    ->label('Banner')
                    ->getStateUsing(fn ($record) => $record->banner_path_url)
                    ->height(60)
                    ->width(100),
                IconColumn::make('status')
                    ->color(fn (string|int $state): string => match ((string) $state) {
                        '1' => 'success',   // Active
                        '0' => 'gray',      // Inactive
                        default => 'secondary',
                    }),

                IconColumn::make('show_live')
                    ->label('Live Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash'),
                TextColumn::make('redirect_link')
                    ->label('Redirect Link'),

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
