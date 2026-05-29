<?php

declare(strict_types=1);

namespace App\Filament\Resources\VendorGamification\Pages;

use App\Filament\Resources\VendorGamification\VendorGamificationResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVendorGamification extends EditRecord
{
    protected static string $resource = VendorGamificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('View')
                ->icon('heroicon-o-eye')
                ->url(fn ($record) => static::getResource()::getUrl('view', ['record' => $record])),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
