<?php

namespace App\Filament\Resources\OrderBillings\Pages;

use App\Filament\Resources\OrderBillings\OrderBillingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderBilling extends CreateRecord
{
    protected static string $resource = OrderBillingResource::class;

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }

    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
