<?php

namespace App\Filament\Resources\OrderCancellations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderCancellationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_id')
                    ->required()
                    ->numeric(),
                Select::make('cancelled_by')
                    ->options(['customer' => 'Customer', 'admin' => 'Admin', 'driver' => 'Driver', 'system' => 'System'])
                    ->required(),
                TextInput::make('cancelled_by_id')
                    ->numeric(),
                TextInput::make('reason_code')
                    ->required(),
                Textarea::make('reason_description')
                    ->columnSpanFull(),
                TextInput::make('refund_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('refund_status')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('cancelled_at')
                    ->required(),
                DateTimePicker::make('refund_processed_at'),
                TextInput::make('processed_by')
                    ->numeric(),
                Textarea::make('admin_notes')
                    ->columnSpanFull(),
            ]);
    }
}
