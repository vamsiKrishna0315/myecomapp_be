<?php

namespace App\Filament\Resources\Drivers\Schemas;

use App\Enums\VehicleType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class DriverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Driver Information')
                    ->description('Basic personal and contact information for the driver')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Full Name')
                                    ->placeholder('Enter driver full name')
                                    ->columnSpan(1),
                                
                                TextInput::make('mobile')
                                    ->required()
                                    ->tel()
                                    ->maxLength(15)
                                    ->unique(ignoreRecord: true)
                                    ->label('Mobile Number')
                                    ->placeholder('e.g., +91 9876543210')
                                    ->columnSpan(1),
                                
                                TextInput::make('email')
                                    ->required()
                                    ->email()
                                    ->unique(ignoreRecord: true)
                                    ->label('Email Address')
                                    ->placeholder('driver@example.com')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('password')
                                    ->password()
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->label('Password')
                                    ->placeholder('Enter secure password')
                                    ->helperText('Leave blank to keep current password when editing')
                                    ->columnSpan(1),
                                
                                TextInput::make('experience_years')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(50)
                                    ->label('Experience (Years)')
                                    ->suffix('years')
                                    ->placeholder('0')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Vehicle Information')
                    ->description('Vehicle details, licensing and insurance information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('vehicle_type')
                                    ->required()
                                    ->options([
                                        VehicleType::Bike->value => 'Bike',
                                        VehicleType::Car->value => 'Car',
                                        VehicleType::Van->value => 'Van',
                                    ])
                                    ->native(false)
                                    ->label('Vehicle Type')
                                    ->columnSpan(1),
                                
                                TextInput::make('vehicle_number')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Vehicle Number')
                                    ->placeholder('e.g., KA 01 AB 1234')
                                    ->columnSpan(1),
                                
                                TextInput::make('license_number')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('License Number')
                                    ->placeholder('e.g., KA1234567890123')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(3)
                            ->schema([
                                DatePicker::make('license_expiry_date')
                                    ->native(false)
                                    ->label('License Expiry Date')
                                    ->helperText('When does the driving license expire?')
                                    ->minDate(now()->toDateString())
                                    ->columnSpan(1),
                                
                                TextInput::make('insurance_number')
                                    ->maxLength(255)
                                    ->label('Insurance Number')
                                    ->placeholder('Insurance policy number')
                                    ->columnSpan(1),
                                
                                DatePicker::make('insurance_expiry_date')
                                    ->native(false)
                                    ->label('Insurance Expiry Date')
                                    ->helperText('When does the vehicle insurance expire?')
                                    ->minDate(now()->toDateString())
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Address Information')
                    ->description('Driver residential address details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('address_line_1')
                                    ->maxLength(255)
                                    ->label('Address Line 1')
                                    ->placeholder('House/Flat number, Building name')
                                    ->columnSpan(1),
                                
                                TextInput::make('address_line_2')
                                    ->maxLength(255)
                                    ->label('Address Line 2')
                                    ->placeholder('Street, Area, Landmark (optional)')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextInput::make('city')
                                    ->maxLength(255)
                                    ->label('City')
                                    ->placeholder('Enter city')
                                    ->columnSpan(1),
                                
                                TextInput::make('state')
                                    ->maxLength(255)
                                    ->label('State')
                                    ->placeholder('Enter state')
                                    ->columnSpan(1),
                                
                                TextInput::make('zip_code')
                                    ->maxLength(255)
                                    ->label('ZIP Code')
                                    ->placeholder('Enter PIN code')
                                    ->columnSpan(1),
                                
                                TextInput::make('country')
                                    ->default('India')
                                    ->maxLength(255)
                                    ->label('Country')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Document Uploads')
                    ->description('Upload driver and vehicle related documents')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                FileUpload::make('profile_image')
                                    ->image()
                                    ->directory('drivers/profiles')
                                    ->visibility('public')
                                    ->label('Profile Photo')
                                    ->helperText('Upload driver profile photo')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(2048)
                                    ->columnSpan(1),
                                
                                FileUpload::make('driver_license_image')
                                    ->image()
                                    ->directory('drivers/licenses')
                                    ->visibility('public')
                                    ->label('Driving License')
                                    ->helperText('Upload driving license document')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                                    ->maxSize(5120)
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                FileUpload::make('vehicle_registration_image')
                                    ->image()
                                    ->directory('drivers/registrations')
                                    ->visibility('public')
                                    ->label('Vehicle Registration')
                                    ->helperText('Upload vehicle registration certificate')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                                    ->maxSize(5120)
                                    ->columnSpan(1),
                                
                                FileUpload::make('insurance_image')
                                    ->image()
                                    ->directory('drivers/insurance')
                                    ->visibility('public')
                                    ->label('Insurance Document')
                                    ->helperText('Upload vehicle insurance certificate')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                                    ->maxSize(5120)
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('Driver Status & Verification')
                    ->description('Driver verification status, availability and account status')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_verified')
                                    ->label('Verified Driver')
                                    ->default(false)
                                    ->helperText('Mark as verified after document verification')
                                    ->columnSpan(1),
                                
                                Toggle::make('is_available')
                                    ->label('Available for Orders')
                                    ->default(true)
                                    ->helperText('Driver availability for new orders')
                                    ->columnSpan(1),
                                
                                Select::make('status')
                                    ->required()
                                    ->options([
                                        0 => 'Inactive',
                                        1 => 'Active',
                                    ])
                                    ->default(1)
                                    ->native(false)
                                    ->label('Account Status')
                                    ->helperText('Active drivers can receive orders')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('rating')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(5)
                                    ->step(0.01)
                                    ->disabled()
                                    ->dehydrated()
                                    ->label('Driver Rating')
                                    ->suffix('/ 5.0')
                                    ->helperText('Auto-calculated from customer reviews')
                                    ->columnSpan(1),
                                
                                TextInput::make('total_deliveries')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->label('Total Deliveries')
                                    ->suffix('orders')
                                    ->helperText('Total completed deliveries')
                                    ->columnSpan(1),
                                
                                TextInput::make('verified_by')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->label('Verified By')
                                    ->helperText('Admin ID who verified the driver')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Current Location & Tracking')
                    ->description('Real-time location tracking for driver availability')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('current_lat')
                                    ->label('Current Latitude')
                                    ->numeric()
                                    ->step(0.0000001)
                                    ->placeholder('e.g., 12.9716')
                                    ->helperText('GPS latitude coordinate')
                                    ->columnSpan(1),
                                
                                TextInput::make('current_lng')
                                    ->label('Current Longitude')
                                    ->numeric()
                                    ->step(0.0000001)
                                    ->placeholder('e.g., 77.5946')
                                    ->helperText('GPS longitude coordinate')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
