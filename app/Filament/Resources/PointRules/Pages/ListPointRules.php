<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointRules\Pages;

use App\Filament\Resources\PointRules\PointRulesResource;
use Filament\Resources\Pages\ListRecords;

class ListPointRules extends ListRecords
{
    protected static string $resource = PointRulesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
