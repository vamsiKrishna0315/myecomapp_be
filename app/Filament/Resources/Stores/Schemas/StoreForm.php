<?php

declare(strict_types=1);

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Store Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('address_1')
                            ->label('Address Line 1')
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('total_address', self::composeAddress($get))),
                        TextInput::make('address_2')
                            ->label('Address Line 2')
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('total_address', self::composeAddress($get))),
                        TextInput::make('city')
                            ->maxLength(100)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('total_address', self::composeAddress($get))),
                        TextInput::make('distict')
                            ->label('District')
                            ->maxLength(100)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('total_address', self::composeAddress($get))),
                        TextInput::make('state')
                            ->maxLength(100)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('total_address', self::composeAddress($get))),
                        TextInput::make('pincode')
                            ->maxLength(20)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('total_address', self::composeAddress($get))),
                        TextInput::make('country')
                            ->default('india')
                            ->maxLength(100)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('total_address', self::composeAddress($get))),
                        TextInput::make('total_address')
                            ->label('Total Address')
                            ->readOnly()
                            ->dehydrated()
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Social & Favicon')
                    ->schema([
                        FileUpload::make('favicon')
                            ->image()
                            ->disk('public')
                            ->directory('stores/favicons')
                            ->maxSize(1024),
                        TextInput::make('facebook')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('instagram')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('twitter')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('linkedin')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('youtube')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Grid::make(2)
                    ->schema([
                        Section::make('Logo')
                            ->schema([
                                FileUpload::make('logo')
                                    ->image()
                                    ->disk('public')
                                    ->directory('stores/logos')
                                    ->maxSize(2048),
                            ])
                            ->columnSpan(1),

                        Section::make('Meta')
                            ->schema([
                                TextInput::make('meta_title')
                                    ->maxLength(255),
                                Textarea::make('meta_description')
                                    ->maxLength(500),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    private static function composeAddress($get)
    {
        $parts = [
            $get('address_1'),
            $get('address_2'),
            $get('city'),
            $get('distict'),
            $get('state'),
            $get('pincode'),
            $get('country'),
        ];

        return collect($parts)
            ->filter()
            ->implode(', ');
    }
}
