<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user_mobile_no')
                    ->label('Mobile')
                    ->searchable(),
                TextColumn::make('user_role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match (true) {
                        str_contains(mb_strtolower($state), 'admin') => 'danger',
                        str_contains(mb_strtolower($state), 'store') => 'warning',
                        str_contains(mb_strtolower($state), 'driver') => 'success',
                        str_contains(mb_strtolower($state), 'vendor') => 'info',
                        default => 'secondary',
                    }),
                TextColumn::make('store.name')
                    ->label('Store')
                    ->sortable(),
                IconColumn::make('status')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('joining_date')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
                TextColumn::make('salary')
                    ->label('Salary')
                    ->money('INR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('user_role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'store_admin' => 'Store Admin',
                        'store_driver' => 'Store Driver',
                        'store_vendor' => 'Store Vendor',
                        'user' => 'User',
                    ]),
                SelectFilter::make('store_id')
                    ->label('Store')
                    ->relationship('store', 'name'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
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
