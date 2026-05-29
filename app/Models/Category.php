<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasMetaTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class Category extends Model
{
    use HasMetaTag;

    protected $fillable = [
        'category_name',
        'category_type',
        'status',
        'is_live',
        'category_image',
    ];

    protected $appends = [
        'category_image_url',
        'slug',
        'seo',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_live' => 'boolean',
    ];

    public static function getCategoryTypes(): array
    {
        return self::query()
            ->distinct()
            ->pluck('category_type', 'category_type')
            ->toArray();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Keep category routing compatible with the current frontend slug derivation.
     */
    public function getSlugAttribute(): string
    {
        return Str::slug($this->category_type ?: $this->category_name);
    }

    public function getSeoCanonicalUrl(): string
    {
        return url('/category/'.$this->slug);
    }

    public function getCategoryImageUrlAttribute()
    {
        if (! $this->category_image) {
            return null;
        }

        return Storage::disk('public')->url($this->category_image);
    }
}
