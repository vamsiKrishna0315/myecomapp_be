<?php

declare(strict_types=1);

namespace App\Filament\Resources\FlashBanners\Pages;

use App\Filament\Resources\FlashBanners\FlashBannerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditFlashBanner extends EditRecord
{
    protected static string $resource = FlashBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
