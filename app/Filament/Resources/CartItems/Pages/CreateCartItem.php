<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartItems\Pages;

use App\Filament\Resources\CartItems\CartItemResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCartItem extends CreateRecord
{
    protected static string $resource = CartItemResource::class;
}
