<?php

declare(strict_types=1);

namespace App\Filament\Resources\FlashBanners\Pages;

use App\Filament\Resources\FlashBanners\FlashBannerResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateFlashBanner extends CreateRecord
{
    protected static string $resource = FlashBannerResource::class;
}
