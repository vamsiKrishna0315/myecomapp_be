<?php

namespace App\Filament\Resources\OrderItems\Pages;

use App\Filament\Resources\OrderItems\OrderItemsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderItems extends CreateRecord
{
    protected static string $resource = OrderItemsResource::class;

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }

    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
