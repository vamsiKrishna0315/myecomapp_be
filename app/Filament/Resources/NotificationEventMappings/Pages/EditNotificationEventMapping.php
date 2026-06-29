<?php

namespace App\Filament\Resources\NotificationEventMappings\Pages;

use App\Filament\Resources\NotificationEventMappings\NotificationEventMappingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNotificationEventMapping extends EditRecord
{
    protected static string $resource = NotificationEventMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
