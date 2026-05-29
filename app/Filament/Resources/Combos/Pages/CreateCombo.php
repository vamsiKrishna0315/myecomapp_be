<?php

declare(strict_types=1);

namespace App\Filament\Resources\Combos\Pages;

use App\Filament\Resources\Combos\ComboResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCombo extends CreateRecord
{
    protected static string $resource = ComboResource::class;

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
