<?php

namespace App\Filament\Resources\CutTypes\Pages;

use App\Filament\Resources\CutTypes\CutTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCutType extends CreateRecord
{
    protected static string $resource = CutTypeResource::class;

    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }
}
