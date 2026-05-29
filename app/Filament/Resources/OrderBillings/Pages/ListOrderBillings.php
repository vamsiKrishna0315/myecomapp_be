<?php

namespace App\Filament\Resources\OrderBillings\Pages;

use App\Filament\Resources\OrderBillings\OrderBillingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderBillings extends ListRecords
{
    protected static string $resource = OrderBillingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
