<?php

namespace App\Filament\Resources\NotificationEventMappings;

use App\Filament\Resources\NotificationEventMappings\Pages\CreateNotificationEventMapping;
use App\Filament\Resources\NotificationEventMappings\Pages\EditNotificationEventMapping;
use App\Filament\Resources\NotificationEventMappings\Pages\ListNotificationEventMappings;
use App\Filament\Resources\NotificationEventMappings\Schemas\NotificationEventMappingForm;
use App\Filament\Resources\NotificationEventMappings\Tables\NotificationEventMappingsTable;
use App\Models\NotificationEventMapping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NotificationEventMappingResource extends Resource
{
    protected static ?string $model = NotificationEventMapping::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'event_type';

    public static function form(Schema $schema): Schema
    {
        return NotificationEventMappingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationEventMappingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotificationEventMappings::route('/'),
            'create' => CreateNotificationEventMapping::route('/create'),
            'edit' => EditNotificationEventMapping::route('/{record}/edit'),
        ];
    }
}
