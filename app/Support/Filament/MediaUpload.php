<?php

declare(strict_types=1);

namespace App\Support\Filament;

use App\Enums\MediaCategory;
use App\Services\Media\MediaService;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class MediaUpload
{
    public static function configure(FileUpload $component, MediaCategory $category): FileUpload
    {
        return $component
            ->disk((string) config('media.local.disk', 'public'))
            ->fetchFileInformation(false)
            ->getUploadedFileUsing(static function (BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                if (blank($file)) {
                    return null;
                }

                return [
                    'name' => ($component->isMultiple() ? Arr::get($storedFileNames, $file) : $storedFileNames) ?? basename($file),
                    'size' => 0,
                    'type' => null,
                    'url' => app(MediaService::class)->publicUrl($file),
                ];
            })
            ->saveUploadedFileUsing(static function (BaseFileUpload $component, TemporaryUploadedFile $file) use ($category): ?string {
                Log::info('Filament media upload requested', [
                    'field' => $component->getName(),
                    'category' => $category->value,
                    'provider' => config('media.default', 'local'),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                ]);

                return app(MediaService::class)->upload($file, $category);
            })
            ->deleteUploadedFileUsing(static function (string $file): void {
                app(MediaService::class)->delete($file);
            });
    }
}
