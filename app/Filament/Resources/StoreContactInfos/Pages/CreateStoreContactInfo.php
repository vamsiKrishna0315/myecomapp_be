<?php

declare(strict_types=1);

namespace App\Filament\Resources\StoreContactInfos\Pages;

use App\Filament\Resources\StoreContactInfos\StoreContactInfoResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateStoreContactInfo extends CreateRecord
{
    protected static string $resource = StoreContactInfoResource::class;
}
