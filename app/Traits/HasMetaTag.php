<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\MetaTag;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasMetaTag
{
    /**
     * Get the model's meta tag.
     */
    public function meta(): MorphOne
    {
        return $this->morphOne(MetaTag::class, 'metaable');
    }

    /**
     * Create or update meta tags for this model.
     */
    public function updateMeta(array $data): MetaTag
    {
        return $this->meta()->updateOrCreate(
            [
                'metaable_id' => $this->id,
                'metaable_type' => get_class($this),
            ],
            $data
        );
    }

    /**
     * Normalize SEO data through the MetaTag serializer for API output.
     */
    public function getSeoAttribute(): array
    {
        $meta = $this->relationLoaded('meta')
            ? $this->getRelation('meta')
            : $this->meta()->first();

        if (! $meta) {
            $meta = new MetaTag();
            $meta->setRelation('metaable', $this);
        }

        return $meta->toSeoArray();
    }

    /**
     * Get meta title or default.
     */
    public function getMetaTitle(): ?string
    {
        return $this->meta?->meta_title ?? $this->name ?? $this->title ?? null;
    }

    /**
     * Get meta description or default.
     */
    public function getMetaDescription(): ?string
    {
        return $this->meta?->meta_description ?? $this->description ?? null;
    }

    /**
     * Get canonical URL.
     */
    public function getCanonicalUrl(): ?string
    {
        return $this->meta?->canonical_url ?? url()->current();
    }
}
