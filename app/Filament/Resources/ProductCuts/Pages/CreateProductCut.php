<?php

namespace App\Filament\Resources\ProductCuts\Pages;

use App\Filament\Resources\ProductCuts\ProductCutResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductCut extends CreateRecord
{
    protected static string $resource = ProductCutResource::class;

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }

    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
