<?php

declare(strict_types=1);

namespace App\Filament\Resources\StoreContactInfos\Pages;

use App\Filament\Resources\StoreContactInfos\StoreContactInfoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditStoreContactInfo extends EditRecord
{
    protected static string $resource = StoreContactInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
