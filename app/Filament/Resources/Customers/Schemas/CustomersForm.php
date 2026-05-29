<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Forms\Get;
use Filament\Forms\Set;

class CustomersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('first_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('First Name'),
                                
                                TextInput::make('last_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Last Name'),
                                
                                DatePicker::make('dob')
                                    ->label('Date of Birth')
                                    ->maxDate(now()->subYears(18))
                                    ->displayFormat('d/m/Y')
                                    ->native(false),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->label('Email'),
                                
                                TextInput::make('mobile')
                                    ->tel()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(15)
                                    ->label('Mobile Number')
                                    ->placeholder('+91 1234567890'),
                                
                                TextInput::make('password')
                                    ->password()
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->revealable()
                                    ->minLength(8)
                                    ->label('Password')
                                    ->helperText('Leave blank to keep current password'),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        1 => 'Active',
                                        0 => 'Inactive',
                                    ])
                                    ->default(1)
                                    ->required()
                                    ->label('Status'),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Addresses')
                    ->schema([
                        Repeater::make('addresses')
                            ->relationship()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('address_line1')
                                            ->required()
                                            ->maxLength(255)
                                            ->label('Address Line 1')
                                            ->columnSpan(2),
                                        
                                        Select::make('address_type')
                                            ->options([
                                                1 => 'Home',
                                                2 => 'Work',
                                                3 => 'Other',
                                            ])
                                            ->default(1)
                                            ->required()
                                            ->label('Address Type'),
                                    ]),
                                
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('address_line2')
                                            ->maxLength(255)
                                            ->label('Address Line 2')
                                            ->placeholder('Apartment, suite, etc. (optional)'),
                                        
                                        TextInput::make('city')
                                            ->required()
                                            ->maxLength(255)
                                            ->label('City'),
                                        
                                        TextInput::make('state')
                                            ->required()
                                            ->maxLength(255)
                                            ->label('State'),
                                    ]),
                                
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('zip_code')
                                            ->required()
                                            ->maxLength(10)
                                            ->label('ZIP / Postal Code'),
                                        
                                        TextInput::make('country')
                                            ->required()
                                            ->default('India')
                                            ->maxLength(255)
                                            ->label('Country'),
                                        
                                        Toggle::make('is_default')
                                            ->label('Default Address')
                                            ->default(false),
                                    ]),
                                
                                Grid::make(3)
                                    ->schema([
                                        Select::make('status')
                                            ->options([
                                                1 => 'Active',
                                                0 => 'Inactive',
                                            ])
                                            ->default(1)
                                            ->required()
                                            ->label('Status'),
                                    ]),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Address')
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['address_line1'] 
                                    ? "{$state['address_line1']}, {$state['city']}" 
                                    : 'New Address'
                            )
                            ->columns(1)
                            ->grid(1),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }
}