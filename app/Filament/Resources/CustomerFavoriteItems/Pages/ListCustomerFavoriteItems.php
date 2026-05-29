<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerFavoriteItems\Pages;

use App\Filament\Resources\CustomerFavoriteItems\CustomerFavoriteItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListCustomerFavoriteItems extends ListRecords
{
    protected static string $resource = CustomerFavoriteItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
