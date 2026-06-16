<?php

declare(strict_types=1);

namespace App\Services\Banners;

use App\Models\Banner;
use App\Services\Media\MediaService;
use Illuminate\Database\Eloquent\Collection;

final class BannerMediaService
{
    public function __construct(
        private readonly MediaService $mediaService
    ) {}

    /**
     * @return Collection<int, Banner>
     */
    public function activeBanners(): Collection
    {
        return Banner::query()
            ->where('status', 1)
            ->where('show_live', 1)
            ->get();
    }

    public function bannerPathUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return $this->mediaService->publicUrl($path);
    }

    public function deleteBannerPath(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        return $this->mediaService->delete($path);
    }
}
