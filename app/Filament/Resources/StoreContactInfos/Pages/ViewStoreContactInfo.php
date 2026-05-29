<?php

declare(strict_types=1);

namespace App\Filament\Resources\StoreContactInfos\Pages;

use App\Filament\Resources\StoreContactInfos\StoreContactInfoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewStoreContactInfo extends ViewRecord
{
    protected static string $resource = StoreContactInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
