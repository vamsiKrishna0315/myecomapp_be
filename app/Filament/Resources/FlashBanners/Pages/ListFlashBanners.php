<?php

declare(strict_types=1);

namespace App\Filament\Resources\FlashBanners\Pages;

use App\Filament\Resources\FlashBanners\FlashBannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListFlashBanners extends ListRecords
{
    protected static string $resource = FlashBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
