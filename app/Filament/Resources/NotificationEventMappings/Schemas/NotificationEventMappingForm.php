<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationEventMappings\Schemas;

use App\Enums\NotificationEventType;
use App\Models\NotificationTemplate;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class NotificationEventMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event Information')
                    ->schema([
                        Select::make('event_type')
                            ->label('Event Type')
                            ->options([
                                NotificationEventType::OTP->value => 'OTP',
                                NotificationEventType::ORDER_STATUS->value => 'Order Status',
                                NotificationEventType::PAYMENT->value => 'Payment',
                                NotificationEventType::REFUND->value => 'Refund',
                                NotificationEventType::WELCOME->value => 'Welcome',
                            ])
                            ->required()
                            ->native(false),

                        Select::make('reference_model')
                            ->label('Reference Model')
                            ->options([
                                'App\Models\OrderStatus' => 'Order Status',
                                'App\Models\PaymentStatus' => 'Payment Status',
                                'App\Models\RefundStatus' => 'Refund Status',
                                'App\Models\Otps' => 'OTP',
                            ])
                            ->searchable()
                            ->native(false),

                        Select::make('notification_template_id')
                            ->label('Notification Template')
                            ->relationship(
                                name: 'template',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('status', 1)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                    ])
                    ->columns(3),

                Section::make('Reference')
                    ->schema([
                        Select::make('reference_id')
                            ->label('Reference')
                            ->options([])
                            ->searchable(),
                    ]),

                Section::make('Additional Information')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                1 => 'Active',
                                0 => 'Inactive',
                            ])
                            ->default(1)
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }
}
