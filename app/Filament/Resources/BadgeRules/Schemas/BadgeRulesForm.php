<?php

declare(strict_types=1);

namespace App\Filament\Resources\BadgeRules\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class BadgeRulesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Badge Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Badge Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Unique name for this badge'),
                                
                                TextInput::make('icon')
                                    ->label('Icon')
                                    ->maxLength(255)
                                    ->helperText('Icon identifier (e.g., first-order)'),
                            ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Describe what this badge represents'),
                    ]),

                Section::make('Badge Configuration')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('trigger_type')
                                    ->label('Trigger Type')
                                    ->options([
                                        'order_count' => 'Order Count',
                                        'reputation_points' => 'Reputation Points',
                                    ])
                                    ->required()
                                    ->helperText('What achievement triggers this badge'),
                                
                                TextInput::make('threshold_value')
                                    ->label('Threshold Value')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->helperText('Value needed to earn badge'),
                                
                                TextInput::make('level')
                                    ->label('Badge Level')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(5)
                                    ->default(1)
                                    ->helperText('Difficulty level (1-5)'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Display order in lists'),
                                
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Enable or disable this badge'),
                            ]),
                    ]),
            ]);
    }
}
