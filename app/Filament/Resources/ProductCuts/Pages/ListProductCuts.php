<?php

namespace App\Filament\Resources\ProductCuts\Pages;

use App\Filament\Resources\ProductCuts\ProductCutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductCuts extends ListRecords
{
    protected static string $resource = ProductCutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
