<?php

namespace App\Filament\Resources\OrderCancellations\Pages;

use App\Filament\Resources\OrderCancellations\OrderCancellationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderCancellation extends EditRecord
{
    protected static string $resource = OrderCancellationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }

    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
