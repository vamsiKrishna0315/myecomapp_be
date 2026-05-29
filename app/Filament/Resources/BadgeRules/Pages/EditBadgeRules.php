<?php

declare(strict_types=1);

namespace App\Filament\Resources\BadgeRules\Pages;

use App\Filament\Resources\BadgeRules\BadgeRulesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBadgeRules extends EditRecord
{
    protected static string $resource = BadgeRulesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
