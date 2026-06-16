<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Banner;
use App\Services\Banners\BannerMediaService;

final class BannerObserver
{
    public function __construct(
        private readonly BannerMediaService $bannerMediaService
    ) {}

    public function deleted(Banner $banner): void
    {
        $this->bannerMediaService->deleteBannerPath($banner->banner_path);
    }
}
