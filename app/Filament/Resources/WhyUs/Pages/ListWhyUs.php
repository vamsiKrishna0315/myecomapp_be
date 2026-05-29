<?php

namespace App\Filament\Resources\WhyUs\Pages;

use App\Filament\Resources\WhyUs\WhyUsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWhyUs extends ListRecords
{
    protected static string $resource = WhyUsResource::class;

    protected static ?string $title = 'Why Us ?';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
