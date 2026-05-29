<?php

declare(strict_types=1);

namespace App\Filament\Resources\StoreContactInfos\Pages;

use App\Filament\Resources\StoreContactInfos\StoreContactInfoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListStoreContactInfos extends ListRecords
{
    protected static string $resource = StoreContactInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
