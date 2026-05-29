<?php

namespace App\Filament\Resources\OrderCancellations\Pages;

use App\Filament\Resources\OrderCancellations\OrderCancellationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderCancellations extends ListRecords
{
    protected static string $resource = OrderCancellationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
