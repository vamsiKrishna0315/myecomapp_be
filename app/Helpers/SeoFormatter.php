<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\MetaTag;

final class SeoFormatter
{
    public static function format(?MetaTag $meta): array
    {
        return $meta?->toSeoArray() ?? [];
    }
}
