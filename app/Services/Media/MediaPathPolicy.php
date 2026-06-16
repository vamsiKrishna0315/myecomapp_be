<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Enums\MediaCategory;
use InvalidArgumentException;

final class MediaPathPolicy
{
    /**
     * @return array<int, string>
     */
    public function publicCategories(): array
    {
        return MediaCategory::publicValues();
    }

    /**
     * @return array<int, string>
     */
    public function privateCategories(): array
    {
        return MediaCategory::privateValues();
    }

    public function assertUploadCategory(MediaCategory $category): MediaCategory
    {
        return $category;
    }

    public function objectKey(MediaCategory $category, string $filename): string
    {
        return $this->join($this->assertUploadCategory($category)->value, $this->normalizeFilename($filename));
    }

    public function normalizeObjectKey(string $path): string
    {
        $path = $this->normalizeSlashes($path);

        if ($path === '') {
            throw new InvalidArgumentException('Media path cannot be empty.');
        }

        $segments = array_values(array_filter(explode('/', $path), static fn (string $segment): bool => $segment !== ''));

        foreach ($segments as $segment) {
            if ($segment === '.' || $segment === '..') {
                throw new InvalidArgumentException('Media paths may not contain traversal segments.');
            }
        }

        return implode('/', $segments);
    }

    public function normalizeFilename(string $filename): string
    {
        $filename = $this->normalizeSlashes($filename);

        if ($filename === '') {
            throw new InvalidArgumentException('Media filename cannot be empty.');
        }

        if (str_contains($filename, '/')) {
            throw new InvalidArgumentException('Media filename must not contain directory separators.');
        }

        if ($filename === '.' || $filename === '..') {
            throw new InvalidArgumentException('Media filename may not be a traversal segment.');
        }

        return $filename;
    }

    private function join(string $directory, string $filename): string
    {
        return trim($directory, '/').'/'.trim($filename, '/');
    }

    private function normalizeSlashes(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));

        if (preg_match('~^[a-z][a-z0-9+.-]*://~i', $path) === 1) {
            throw new InvalidArgumentException('Media paths must be relative object keys.');
        }

        return trim($path, '/');
    }
}
