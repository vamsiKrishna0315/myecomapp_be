<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerFavoriteItems\Pages;

use App\Filament\Resources\CustomerFavoriteItems\CustomerFavoriteItemResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCustomerFavoriteItem extends CreateRecord
{
    protected static string $resource = CustomerFavoriteItemResource::class;
}
