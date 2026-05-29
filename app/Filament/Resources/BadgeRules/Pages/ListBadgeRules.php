<?php

declare(strict_types=1);

namespace App\Filament\Resources\BadgeRules\Pages;

use App\Filament\Resources\BadgeRules\BadgeRulesResource;
use Filament\Resources\Pages\ListRecords;

class ListBadgeRules extends ListRecords
{
    protected static string $resource = BadgeRulesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
