<?php

namespace App\Filament\Resources\OrderStatuses\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use App\Models\OrderStatuses;

class OrderStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20)
                                    ->label('Status Code')
                                    ->placeholder('e.g., PENDING, CONFIRMED')
                                    ->helperText('Unique identifier for the status')
                                    ->columnSpan(1),

                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(100)
                                    ->label('Status Name')
                                    ->placeholder('e.g., Order Pending, Confirmed')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                ColorPicker::make('color')
                                    ->label('Status Color')
                                    ->default('#3B82F6')
                                    ->helperText('Color for status badge display')
                                    ->columnSpan(1),

                                TextInput::make('sequence')
                                    ->numeric()
                                    ->default(fn () => (OrderStatuses::max('sequence') ?? 0) + 1)
                                    ->unique(ignoreRecord: true)
                                    ->minValue(1)
                                    ->label('Sequence')
                                    ->helperText('Order sequence for status progression')
                                    ->columnSpan(1),
                            ]),

                        Textarea::make('description')
                            ->maxLength(500)
                            ->label('Description')
                            ->placeholder('Detailed description of this status...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Status Configuration')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_final')
                                    ->label('Is Final Status')
                                    ->default(false)
                                    ->helperText('Cannot be changed once reached')
                                    ->columnSpan(1),

                                Toggle::make('is_cancellable')
                                    ->label('Is Cancellable')
                                    ->default(true)
                                    ->helperText('Orders can be cancelled from this status')
                                    ->columnSpan(1),

                                TextInput::make('display_order')
                                    ->numeric()
                                    ->default(fn () => (OrderStatuses::max('display_order') ?? 0) + 1)
                                    ->unique(ignoreRecord: true)
                                    ->minValue(1)
                                    ->label('Display Order')
                                    ->helperText('Order for UI display')
                                    ->columnSpan(1),
                            ]),

                        Select::make('status')
                            ->options([
                                1 => 'Active',
                                0 => 'Inactive',
                            ])
                            ->default(1)
                            ->required()
                            ->label('Status')
                            ->helperText('Whether this status is currently active'),
                    ])
                    ->columns(1),
            ]);
    }
}
