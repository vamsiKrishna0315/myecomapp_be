<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointRules\Pages;

use App\Filament\Resources\PointRules\PointRulesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPointRules extends EditRecord
{
    protected static string $resource = PointRulesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
