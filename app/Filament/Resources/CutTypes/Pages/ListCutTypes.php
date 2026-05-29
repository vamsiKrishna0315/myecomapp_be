<?php

namespace App\Filament\Resources\CutTypes\Pages;

use App\Filament\Resources\CutTypes\CutTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCutTypes extends ListRecords
{
    protected static string $resource = CutTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
