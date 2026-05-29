<?php

namespace App\Filament\Resources\OrderBillings\Pages;

use App\Filament\Resources\OrderBillings\OrderBillingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderBilling extends EditRecord
{
    protected static string $resource = OrderBillingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }
}
