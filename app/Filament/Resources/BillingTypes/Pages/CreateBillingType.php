<?php

namespace App\Filament\Resources\BillingTypes\Pages;

use App\Filament\Resources\BillingTypes\BillingTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBillingType extends CreateRecord
{
    protected static string $resource = BillingTypeResource::class;
    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }
}
