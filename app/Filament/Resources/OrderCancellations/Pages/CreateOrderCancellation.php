<?php

namespace App\Filament\Resources\OrderCancellations\Pages;

use App\Filament\Resources\OrderCancellations\OrderCancellationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderCancellation extends CreateRecord
{
    protected static string $resource = OrderCancellationResource::class;

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }

    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
