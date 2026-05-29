<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactInquiries\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class ContactInquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'first_name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            // Customer selected - fetch and populate data
                            $customer = \App\Models\Customer::find($state);
                            if ($customer) {
                                $set('name', $customer->first_name.' '.$customer->last_name);
                                $set('phone', $customer->mobile);
                                $set('email', $customer->email);
                                $set('inquiry_type', 'customer');
                            }
                        } else {
                            // Customer deselected - clear fields and set as guest
                            $set('name', null);
                            $set('phone', null);
                            $set('email', null);
                            $set('inquiry_type', 'guest');
                        }
                    }),
                TextInput::make('name')
                    ->required(fn ($get) => ! $get('customer_id'))
                    ->disabled(fn ($get) => (bool) $get('customer_id'))
                    ->dehydrated(),
                TextInput::make('phone')
                    ->tel()
                    ->required(fn ($get) => ! $get('customer_id'))
                    ->disabled(fn ($get) => (bool) $get('customer_id'))
                    ->dehydrated(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->disabled(fn ($get) => (bool) $get('customer_id'))
                    ->dehydrated(),
                TextInput::make('subject')
                    ->required(),
                Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                Select::make('inquiry_type')
                    ->options(['customer' => 'Customer', 'guest' => 'Guest'])
                    ->default('guest')
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        0 => 'New',
                        1 => 'In Progress',
                        2 => 'Resolved',
                        3 => 'Closed',
                    ])
                    ->default(0)
                    ->required(),
                Textarea::make('admin_response')
                    ->columnSpanFull(),
                DateTimePicker::make('responded_at'),
            ]);
    }
}
