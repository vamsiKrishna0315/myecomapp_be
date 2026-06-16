<?php

declare(strict_types=1);

namespace App\Services\Media\Providers;

use App\Enums\MediaCategory;
use App\Services\Media\MediaProviderInterface;
use App\Services\Media\MediaPathPolicy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class SupabaseProvider implements MediaProviderInterface
{
    public function __construct(
        private readonly MediaPathPolicy $pathPolicy
    ) {}

    public function publicUrl(string $path): string
    {
        return rtrim($this->projectUrl(), '/').'/storage/v1/object/public/'.$this->bucket().'/'.$this->pathPolicy->normalizeObjectKey($path);
    }

    public function upload(UploadedFile $file, MediaCategory $category, ?string $filename = null): string
    {
        $filename = $this->pathPolicy->normalizeFilename($filename ?: $file->hashName());
        $path = $this->pathPolicy->objectKey($category, $filename);
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw new RuntimeException('Unable to read uploaded file contents for Supabase upload.');
        }

        Log::info('Supabase media upload started', [
            'category' => $category->value,
            'bucket' => $this->bucket(),
            'object_key' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
        ]);

        $response = Http::baseUrl($this->projectUrl())
            ->withToken($this->serviceRoleKey())
            ->withHeaders([
                'x-upsert' => 'true',
            ])
            ->withBody($contents, $file->getMimeType() ?: 'application/octet-stream')
            ->post($this->storagePathFor($path));

        if (! $response->successful()) {
            Log::warning('Supabase media upload failed', [
                'category' => $category->value,
                'bucket' => $this->bucket(),
                'object_key' => $path,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            throw new RuntimeException('Supabase media upload failed: '.$response->body());
        }

        Log::info('Supabase media upload finished', [
            'category' => $category->value,
            'bucket' => $this->bucket(),
            'object_key' => $path,
            'status' => $response->status(),
        ]);

        return $path;
    }

    public function delete(string $path): bool
    {
        $objectKey = $this->pathPolicy->normalizeObjectKey($path);

        Log::debug('Supabase media delete requested', [
            'bucket' => $this->bucket(),
            'object_key' => $objectKey,
        ]);

        $response = Http::baseUrl($this->projectUrl())
            ->withToken($this->serviceRoleKey())
            ->delete($this->storagePathFor($objectKey));

        return $response->successful() || $response->status() === 404;
    }

    public function exists(string $path): bool
    {
        $objectKey = $this->pathPolicy->normalizeObjectKey($path);

        Log::debug('Supabase media existence check requested', [
            'bucket' => $this->bucket(),
            'object_key' => $objectKey,
        ]);

        $response = Http::baseUrl($this->projectUrl())
            ->withToken($this->serviceRoleKey())
            ->head($this->storagePathFor($objectKey));

        return $response->successful();
    }

    private function projectUrl(): string
    {
        $url = (string) config('media.supabase.url', '');

        if ($url === '') {
            throw new RuntimeException('SUPABASE_URL is required when MEDIA_PROVIDER=supabase.');
        }

        return $url;
    }

    private function serviceRoleKey(): string
    {
        $key = (string) config('media.supabase.service_role_key', '');

        if ($key === '') {
            throw new RuntimeException('SUPABASE_SERVICE_ROLE_KEY is required when MEDIA_PROVIDER=supabase.');
        }

        return $key;
    }

    private function bucket(): string
    {
        $bucket = (string) config('media.supabase.public_bucket', 'yumeat-assets');

        if ($bucket === '') {
            throw new RuntimeException('SUPABASE_PUBLIC_BUCKET is required when MEDIA_PROVIDER=supabase.');
        }

        return $bucket;
    }

    private function storagePathFor(string $path): string
    {
        return '/'.trim((string) config('media.supabase.storage_path', 'storage/v1/object'), '/')
            .'/'.$this->bucket().'/'.$this->normalizePath($path);
    }

    private function normalizePath(string $path): string
    {
        $path = $this->pathPolicy->normalizeObjectKey($path);

        return $path;
    }
}
