<?php

declare(strict_types=1);

namespace App\Services\Media\Providers;

use App\Enums\MediaCategory;
use App\Services\Media\MediaProviderInterface;
use App\Services\Media\MediaPathPolicy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class LocalProvider implements MediaProviderInterface
{
    public function __construct(
        private readonly MediaPathPolicy $pathPolicy
    ) {}

    public function publicUrl(string $path): string
    {
        return Storage::disk($this->disk())->url($this->pathPolicy->normalizeObjectKey($path));
    }

    public function upload(UploadedFile $file, MediaCategory $category, ?string $filename = null): string
    {
        $filename = $this->pathPolicy->normalizeFilename($filename ?: $file->hashName());
        $directory = $this->pathPolicy->assertUploadCategory($category)->value;
        $path = $this->pathPolicy->objectKey($category, $filename);

        Storage::disk($this->disk())->putFileAs($directory, $file, $filename);

        return $path;
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk())->delete($this->pathPolicy->normalizeObjectKey($path));
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk())->exists($this->pathPolicy->normalizeObjectKey($path));
    }

    private function disk(): string
    {
        return (string) config('media.local.disk', 'public');
    }
}
