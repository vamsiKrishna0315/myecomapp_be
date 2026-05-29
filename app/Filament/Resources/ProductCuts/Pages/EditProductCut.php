<?php

namespace App\Filament\Resources\ProductCuts\Pages;

use App\Filament\Resources\ProductCuts\ProductCutResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductCut extends EditRecord
{
    protected static string $resource = ProductCutResource::class;

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }

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
}
