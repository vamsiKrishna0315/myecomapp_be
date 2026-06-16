<?php

declare(strict_types=1);

namespace App\Services\Media\Providers;

use App\Enums\MediaCategory;
use App\Services\Media\MediaProviderInterface;
use App\Services\Media\MediaPathPolicy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
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

        Log::info('Local media upload started', [
            'disk' => $this->disk(),
            'category' => $category->value,
            'directory' => $directory,
            'object_key' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
        ]);

        Storage::disk($this->disk())->putFileAs($directory, $file, $filename);

        Log::info('Local media upload finished', [
            'disk' => $this->disk(),
            'category' => $category->value,
            'directory' => $directory,
            'object_key' => $path,
        ]);

        return $path;
    }

    public function delete(string $path): bool
    {
        $objectKey = $this->pathPolicy->normalizeObjectKey($path);

        Log::debug('Local media delete requested', [
            'disk' => $this->disk(),
            'object_key' => $objectKey,
        ]);

        $deleted = Storage::disk($this->disk())->delete($objectKey);

        Log::debug('Local media delete completed', [
            'disk' => $this->disk(),
            'object_key' => $objectKey,
            'deleted' => $deleted,
        ]);

        return $deleted;
    }

    public function exists(string $path): bool
    {
        $objectKey = $this->pathPolicy->normalizeObjectKey($path);

        Log::debug('Local media existence check requested', [
            'disk' => $this->disk(),
            'object_key' => $objectKey,
        ]);

        $exists = Storage::disk($this->disk())->exists($objectKey);

        Log::debug('Local media existence check completed', [
            'disk' => $this->disk(),
            'object_key' => $objectKey,
            'exists' => $exists,
        ]);

        return $exists;
    }

    private function disk(): string
    {
        return (string) config('media.local.disk', 'public');
    }
}
