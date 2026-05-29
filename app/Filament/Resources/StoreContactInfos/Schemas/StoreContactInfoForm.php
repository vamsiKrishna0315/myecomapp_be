<?php

declare(strict_types=1);

namespace App\Filament\Resources\StoreContactInfos\Schemas;

use App\Models\Store;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class StoreContactInfoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('store_id')
                    ->label('Store')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $store = Store::find($state);
                            if ($store) {
                                $set('phone', $store->phone);
                                $set('email', $store->email);
                                $set('whatsapp_number', $store->phone);
                                $set('business_hours', 'Monday - Sunday: 6:00 AM - 11:00 PM');
                            }
                        }
                    }),
                TextInput::make('phone')
                    ->tel()
                    ->live(),
                TextInput::make('whatsapp_number')
                    ->live(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->live(),
                TextInput::make('bulk_order_email')
                    ->email(),
                TextInput::make('partnership_email')
                    ->email(),
                Textarea::make('business_hours')
                    ->columnSpanFull(),
                Textarea::make('whatsapp_message')
                    ->columnSpanFull(),
                Toggle::make('status')
                    ->label('Active Status')
                    ->default(true)
                    ->onColor('success')
                    ->offColor('danger'),
                Toggle::make('show_live')
                    ->label('Show Live')
                    ->default(true)
                    ->onColor('success')
                    ->offColor('danger'),
            ]);
    }
}
