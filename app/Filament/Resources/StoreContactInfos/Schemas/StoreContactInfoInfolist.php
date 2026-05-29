<?php

declare(strict_types=1);

namespace App\Filament\Resources\StoreContactInfos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class StoreContactInfoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('store.name')
                    ->label('Store')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-')
                    ->icon('heroicon-o-phone'),
                TextEntry::make('whatsapp_number')
                    ->label('WhatsApp Number')
                    ->placeholder('-')
                    ->icon('heroicon-o-chat-bubble-left-right'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-')
                    ->icon('heroicon-o-envelope'),
                TextEntry::make('bulk_order_email')
                    ->label('Bulk order email')
                    ->placeholder('-'),
                TextEntry::make('partnership_email')
                    ->label('Partnership email')
                    ->placeholder('-'),
                TextEntry::make('business_hours')
                    ->label('Business hours')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('whatsapp_message')
                    ->label('Whatsapp message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? 'Active' : 'Inactive')
                    ->badge()
                    ->color(fn (int $state): string => $state === 1 ? 'success' : 'danger'),
                TextEntry::make('show_live')
                    ->label('Show live')
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? 'Active' : 'Inactive')
                    ->badge()
                    ->color(fn (int $state): string => $state === 1 ? 'success' : 'gray'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
