<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Media\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class MetaTag extends Model
{
    protected $appends = [
        'show_live',
        'og_image_url',
        'seo',
        'twitter_image_url',
    ];

    protected $fillable = [
        'metaable_id',
        'metaable_type',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'canonical_url',
        'robots',
        'priority',
        'changefreq',
        'noindex',
        'nofollow',
        'json_ld',
        'status',
        'show_live',
    ];

    protected $casts = [
        'status' => 'boolean',
        'noindex' => 'boolean',
        'nofollow' => 'boolean',
        'priority' => 'decimal:1',
    ];

    /**
     * Polymorphic relation
     */
    public function metaable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get JSON-LD as array
     */
    public function getJsonLdAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    /**
     * Backward-compatible live flag alias for older callers that expect show_live.
     */
    public function getShowLiveAttribute(): bool
    {
        return (bool) ($this->status ?? true);
    }

    /**
     * Map legacy show_live writes onto the actual status column.
     */
    public function setShowLiveAttribute($value): void
    {
        $this->attributes['status'] = (int) (bool) $value;
    }

    /**
     * Set JSON-LD properly
     */
    public function setJsonLdAttribute($value): void
    {
        $this->attributes['json_ld'] = is_array($value)
            ? json_encode($value)
            : $value;
    }

    /**
     * Get meta title with fallback
     */
    public function getTitle(): ?string
    {
        return $this->meta_title
            ?? $this->metaable?->title
            ?? $this->metaable?->name
            ?? null;
    }

    /**
     * Get meta description with fallback
     */
    public function getDescription(): ?string
    {
        return $this->meta_description
            ?? $this->metaable?->description
            ?? $this->metaable?->short_description
            ?? null;
    }

    /**
     * Get robots directive (index, follow)
     */
    public function getRobots(): string
    {
        if (filled($this->robots)) {
            return $this->robots;
        }

        return ($this->noindex ? 'noindex' : 'index').', '.
               ($this->nofollow ? 'nofollow' : 'follow');
    }

    /**
     * Get canonical URL (fallback to current URL)
     */
    public function getCanonical(): ?string
    {
        if (filled($this->canonical_url)) {
            return $this->canonical_url;
        }

        if ($this->metaable && method_exists($this->metaable, 'getSeoCanonicalUrl')) {
            return $this->metaable->getSeoCanonicalUrl();
        }

        return url()->current();
    }

    /**
     * Get OpenGraph title
     */
    public function getOgTitle(): ?string
    {
        return $this->og_title ?? $this->getTitle();
    }

    /**
     * Get OpenGraph description
     */
    public function getOgDescription(): ?string
    {
        return $this->og_description ?? $this->getDescription();
    }

    /**
     * Get Twitter title
     */
    public function getTwitterTitle(): ?string
    {
        return $this->twitter_title ?? $this->getTitle();
    }

    /**
     * Get Twitter description
     */
    public function getTwitterDescription(): ?string
    {
        return $this->twitter_description ?? $this->getDescription();
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->resolveMediaUrl($this->og_image);
    }

    public function getTwitterImageUrlAttribute(): ?string
    {
        return $this->resolveMediaUrl($this->twitter_image ?: $this->og_image);
    }

    /**
     * Convert full SEO into array (useful for API)
     */
    public function toSeoArray(): array
    {
        return [
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'keywords' => $this->meta_keywords,

            'robots' => $this->getRobots(),
            'canonical' => $this->getCanonical(),

            'openGraph' => [
                'title' => $this->getOgTitle(),
                'description' => $this->getOgDescription(),
                'image' => $this->og_image_url,
                'type' => $this->og_type ?? 'website',
            ],

            'twitter' => [
                'card' => $this->twitter_card ?? 'summary_large_image',
                'title' => $this->getTwitterTitle(),
                'description' => $this->getTwitterDescription(),
                'image' => $this->twitter_image_url,
            ],

            'json_ld' => $this->json_ld,
        ];
    }

    /**
     * Append the normalized serializer beside raw meta fields for API responses.
     */
    public function getSeoAttribute(): array
    {
        return $this->toSeoArray();
    }

    private function resolveMediaUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return app(MediaService::class)->publicUrl($path);
    }
}
