<?php

namespace App\Filament\Resources\WhyUs\Pages;

use App\Filament\Resources\WhyUs\WhyUsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWhyUs extends CreateRecord
{
    protected static string $resource = WhyUsResource::class;

    protected static ?string $title = 'Create Why Us?';

    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }
}
