<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Str;

final class ProductSlugSupport
{
    private const MIN_LENGTH = 8;

    private const MAX_LENGTH = 80;

    private const PASSING_SCORE = 70;

    public static function slugify(?string $value): string
    {
        $slug = Str::slug((string) $value);

        return $slug !== '' ? $slug : 'product';
    }

    public static function generateUnique(?string $preferredValue, ?int $ignoreProductId = null): string
    {
        $baseSlug = self::slugify($preferredValue);
        $slug = $baseSlug;
        $suffix = 2;

        while (self::slugExists($slug, $ignoreProductId)) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public static function normalizeForSave(?string $slug, ?string $fallbackName, ?int $ignoreProductId = null): string
    {
        $preferredValue = filled($slug) ? $slug : $fallbackName;

        return self::generateUnique($preferredValue, $ignoreProductId);
    }

    /**
     * @return array{slug:string,score:int,status:string,reasons:list<string>}
     */
    public static function evaluate(?string $slug): array
    {
        $normalized = self::slugify($slug);
        $score = 100;
        $reasons = [];

        $length = mb_strlen($normalized);
        $segments = array_values(array_filter(explode('-', $normalized)));

        if ($length < self::MIN_LENGTH) {
            $score -= 35;
            $reasons[] = 'Too short. Aim for at least 8 characters.';
        }

        if ($length > self::MAX_LENGTH) {
            $score -= 20;
            $reasons[] = 'Too long. Keep it under 80 characters.';
        }

        if (count($segments) < 2) {
            $score -= 20;
            $reasons[] = 'Use at least two descriptive words.';
        }

        if (! preg_match('/[a-z]/', $normalized)) {
            $score -= 40;
            $reasons[] = 'Include alphabetic keywords, not only numbers.';
        }

        if (preg_match('/\b(?:fresh|best|new|item|product)\b/', $normalized)) {
            $score -= 10;
            $reasons[] = 'Avoid generic filler words where possible.';
        }

        $score = max(0, min(100, $score));

        return [
            'slug' => $normalized,
            'score' => $score,
            'status' => $score >= self::PASSING_SCORE ? 'pass' : 'fail',
            'reasons' => $reasons,
        ];
    }

    public static function qualitySummary(?string $slug): string
    {
        $result = self::evaluate($slug);
        $label = Str::upper($result['status']);
        $summary = "Slug quality: {$label} ({$result['score']}/100).";

        if ($result['reasons'] === []) {
            return "{$summary} Good canonical candidate.";
        }

        return $summary.' '.implode(' ', $result['reasons']);
    }

    private static function slugExists(string $slug, ?int $ignoreProductId = null): bool
    {
        return Product::query()
            ->when($ignoreProductId, fn ($query) => $query->whereKeyNot($ignoreProductId))
            ->where('slug', $slug)
            ->exists();
    }
}
