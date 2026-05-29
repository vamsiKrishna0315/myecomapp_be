<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointRules\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;

class PointRulesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Point Rule Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('event_type')
                                    ->label('Event Type')
                                    ->options([
                                        'order_created' => 'Order Created',
                                        'order_completed' => 'Order Completed',
                                        'order_delivered' => 'Order Delivered',
                                        'high_value_order' => 'High Value Order',
                                        'daily_streak' => 'Daily Streak',
                                    ])
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('The event that triggers point award'),
                                
                                TextInput::make('name')
                                    ->label('Display Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Human-readable name for this rule'),
                            ]),
                    ]),

                Section::make('Point Configuration')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('points')
                                    ->label('Points to Award')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->default(10)
                                    ->helperText('Number of points to award'),
                                
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Enable or disable this rule'),
                            ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Explain when these points are awarded'),
                    ]),

                Section::make('Conditions (Optional)')
                    ->description('Add conditions like minimum order value')
                    ->schema([
                        KeyValue::make('conditions')
                            ->label('Conditions')
                            ->keyLabel('Condition')
                            ->valueLabel('Value')
                            ->helperText('e.g., min_order_value: 5000'),
                    ]),
            ]);
    }
}
