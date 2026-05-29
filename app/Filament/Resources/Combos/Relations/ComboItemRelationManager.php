<?php

declare(strict_types=1);

namespace App\Filament\Resources\Combos\Relations;

use App\Models\CutType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ComboItemRelationManager extends RelationManager
{
    protected static string $relationship = 'comboItems';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('cut_type_id')
                    ->label('Cut Type')
                    ->options(CutType::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('weight')
                    ->numeric()
                    ->required()
                    ->step(0.01),
                Select::make('weight_unit')
                    ->options([
                        'kg' => 'kg',
                        'g' => 'g',
                        'lb' => 'lb',
                    ])
                    ->default('kg')
                    ->required(),
                TextInput::make('quantity')
                    ->numeric()
                    ->integer()
                    ->default(1)
                    ->required(),
                TextInput::make('item_price')
                    ->numeric()
                    ->prefix('₹')
                    ->step(0.01),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable(),
                TextColumn::make('cutType.name')
                    ->label('Cut Type')
                    ->sortable(),
                TextColumn::make('weight')
                    ->suffix(fn ($record) => ' '.$record->weight_unit),
                TextColumn::make('quantity'),
                TextColumn::make('item_price')
                    ->money('INR'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Combo Item')
                    ->modalHeading('Add Combo Item')
                    ->modalSubmitActionLabel('Add Item')
                    ->modalWidth('lg'),
            ])
            ->actions([
                EditAction::make()
                    ->modalHeading('Edit Combo Item')
                    ->modalSubmitActionLabel('Save Changes')
                    ->modalWidth('lg'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
