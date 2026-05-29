<?php

namespace App\Filament\Resources\Stores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular(),
                
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('phone')
                    ->searchable(),
                
                TextColumn::make('total_address')
                    ->label('Address')
                    ->wrap()
                    ->searchable(),
                
                IconColumn::make('social_media')
                    ->label('Social Media')
                    ->icons([
                        'heroicon-o-globe-alt' => fn ($record) => $record->facebook !== null,
                        'heroicon-o-camera' => fn ($record) => $record->instagram !== null,
                        'heroicon-o-chat' => fn ($record) => $record->twitter !== null,
                        'heroicon-o-briefcase' => fn ($record) => $record->linkedin !== null,
                        'heroicon-o-play' => fn ($record) => $record->youtube !== null,
                    ]),
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
