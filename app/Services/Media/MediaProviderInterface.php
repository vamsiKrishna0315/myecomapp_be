<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Enums\MediaCategory;
use Illuminate\Http\UploadedFile;

interface MediaProviderInterface
{
    public function publicUrl(string $path): string;

    public function upload(UploadedFile $file, MediaCategory $category, ?string $filename = null): string;

    public function delete(string $path): bool;

    public function exists(string $path): bool;
}
