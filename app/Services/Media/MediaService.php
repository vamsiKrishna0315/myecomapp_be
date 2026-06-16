<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Enums\MediaCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

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
        $category = $this->pathPolicy->assertUploadCategory($category);

        Log::info('Media service upload requested', [
            'provider' => $this->provider::class,
            'category' => $category->value,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        $path = $this->provider->upload($file, $category, $filename);

        Log::info('Media service upload completed', [
            'provider' => $this->provider::class,
            'category' => $category->value,
            'path' => $path,
        ]);

        return $path;
    }

    public function delete(string $path): bool
    {
        $normalizedPath = $this->pathPolicy->normalizeObjectKey($path);

        Log::debug('Media service delete requested', [
            'provider' => $this->provider::class,
            'path' => $normalizedPath,
        ]);

        $deleted = $this->provider->delete($normalizedPath);

        Log::debug('Media service delete completed', [
            'provider' => $this->provider::class,
            'path' => $normalizedPath,
            'deleted' => $deleted,
        ]);

        return $deleted;
    }

    public function exists(string $path): bool
    {
        $normalizedPath = $this->pathPolicy->normalizeObjectKey($path);

        Log::debug('Media service existence check requested', [
            'provider' => $this->provider::class,
            'path' => $normalizedPath,
        ]);

        $exists = $this->provider->exists($normalizedPath);

        Log::debug('Media service existence check completed', [
            'provider' => $this->provider::class,
            'path' => $normalizedPath,
            'exists' => $exists,
        ]);

        return $exists;
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
