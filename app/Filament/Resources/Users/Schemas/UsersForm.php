<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;  // Add this import
use Filament\Schemas\Schema;

final class UsersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->minLength(8),
                TextInput::make('password_confirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(false)
                    ->same('password'),
                TextInput::make('user_mobile_no')
                    ->label('Mobile Number')
                    ->tel()
                    ->maxLength(20),
                TextInput::make('alternate_number')
                    ->label('Alternate Number')
                    ->tel()
                    ->maxLength(20),
                TextInput::make('address')
                    ->label('Address')
                    ->maxLength(255)
                    ->live(),

                FileUpload::make('address_proof')
                    ->label('Address Proof Document')
                    ->disk('public')
                    ->directory('users/address-proofs')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(5120) // 5MB
                    ->helperText('Upload PDF or image files (max 5MB)')
                    ->required(function (callable $get) {
                        return filled($get('address'));
                    })
                    ->requiredWith('address'),

                Select::make('user_role')
                    ->label('User Role')
                    ->options([
                        'admin' => 'Admin',
                        'store_admin' => 'Store Admin',
                        'store_driver' => 'Store Driver',
                        'store_vendor' => 'Store Vendor',
                        'user' => 'User',
                    ])
                    ->default('user')
                    ->required()
                    ->searchable(),
                Select::make('store_id')
                    ->label('Store')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload(),
                DatePicker::make('joining_date')
                    ->label('Joining Date'),
                TextInput::make('salary')
                    ->label('Salary')
                    ->numeric()
                    ->prefix('Rs.')
                    ->maxValue(999999.99),
                DatePicker::make('dob')
                    ->label('Date of Birth'),
                TextInput::make('finger_print')
                    ->label('Fingerprint Data')
                    ->maxLength(255),
                Toggle::make('status')
                    ->label('Active Status')
                    ->default(true),
            ]);
    }
}
