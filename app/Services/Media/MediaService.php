<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Enums\MediaCategory;
use Illuminate\Http\UploadedFile;

final class MediaService
{
    public function __construct(
        private readonly MediaProviderInterface $provider,
        private readonly MediaPathPolicy $pathPolicy
    ) {}

    public function publicUrl(string $path): string
    {
        return $this->provider->publicUrl($this->pathPolicy->normalizeObjectKey($path));
    }

    public function upload(UploadedFile $file, MediaCategory $category, ?string $filename = null): string
    {
        return $this->provider->upload($file, $this->pathPolicy->assertUploadCategory($category), $filename);
    }

    public function delete(string $path): bool
    {
        return $this->provider->delete($this->pathPolicy->normalizeObjectKey($path));
    }

    public function exists(string $path): bool
    {
        return $this->provider->exists($this->pathPolicy->normalizeObjectKey($path));
    }

    /**
     * @return array<int, string>
     */
    public function publicCategories(): array
    {
        return $this->pathPolicy->publicCategories();
    }

    /**
     * @return array<int, string>
     */
    public function privateCategories(): array
    {
        return $this->pathPolicy->privateCategories();
    }
}
