<?php

namespace App\Filament\Resources\NotificationEventMappings\Pages;

use App\Filament\Resources\NotificationEventMappings\NotificationEventMappingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNotificationEventMappings extends ListRecords
{
    protected static string $resource = NotificationEventMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
