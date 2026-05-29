<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerFavoriteItems\Pages;

use App\Filament\Resources\CustomerFavoriteItems\CustomerFavoriteItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditCustomerFavoriteItem extends EditRecord
{
    protected static string $resource = CustomerFavoriteItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
