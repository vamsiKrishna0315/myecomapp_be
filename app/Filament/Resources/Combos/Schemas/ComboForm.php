<?php

declare(strict_types=1);

namespace App\Filament\Resources\Combos\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class ComboForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Combo Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('status')
                                    ->options([
                                        1 => 'Active',
                                        0 => 'Inactive',
                                    ])
                                    ->default(1)
                                    ->required(),
                            ]),

                        Textarea::make('description')
                            ->maxLength(65535)
                            ->rows(3),
                    ]),

                Section::make('Pricing & Visibility')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('total_price')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->required(),

                                Select::make('currency')
                                    ->options([
                                        'INR' => 'INR',
                                    ])
                                    ->default('INR')
                                    ->required(),

                                TextInput::make('display_order')
                                    ->numeric()
                                    ->integer()
                                    ->default(0)
                                    ->minValue(0),
                            ]),

                        Toggle::make('is_visible')
                            ->label('Visible to Customers')
                            ->default(true),
                    ]),

                Section::make('Combo Items')
                    ->visible(fn (string $context): bool => $context === 'create')
                    ->schema([
                        Placeholder::make('combo_items_hint')
                            ->label('Add Items')
                            ->content(fn (Get $get): string => $get('name')
                            ? 'After saving, open this combo in Edit and use the "Add Combo Item" modal to add products.'
                            : 'Fill combo details and save first, then open Edit to add products via "Add Combo Item" modal.'),
                    ]),
            ]);
    }
}
