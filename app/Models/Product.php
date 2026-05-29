<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasMetaTag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class Product extends Model
{
    use HasFactory;
    use HasMetaTag;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'slug',
        'description',
        'long_description',
        'specifications',
        'price',
        'price_per_kg',
        'price_per_piece',
        'cost',
        'compare_at_price',
        'currency',
        'stock_quantity',
        'stock_unit',
        'low_stock_threshold',
        'track_inventory',
        'requires_advance_order',
        'preparation_time',
        // 'brand_id',
        'tags',
        'primary_image',
        'images',
        'status',
        'is_best_seller',
        'popular',
        'display_order',
        'is_visible',
        'published_at',
        'meta_title',
        'meta_description',
        'weight',
        'minimum_weight',
        'weight_unit',
        'net_weight',
        'dimensions',
        'preparation_style',
        'is_cleaned',
        'is_skinless',
        'protein_per_100g',
        'fat_per_100g',
        'calories_per_100g',
    ];

    protected $casts = [
        'specifications' => 'array',
        'tags' => 'array',
        'images' => 'array',
        'dimensions' => 'array',
        'track_inventory' => 'boolean',
        'is_visible' => 'boolean',
        'is_best_seller' => 'boolean',
        'popular' => 'boolean',
        'is_cleaned' => 'boolean',
        'is_skinless' => 'boolean',
        'requires_advance_order' => 'boolean',
        'published_at' => 'datetime',
        'price' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'price_per_piece' => 'decimal:2',
        'cost' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'minimum_weight' => 'decimal:2',
        'net_weight' => 'decimal:2',
        'protein_per_100g' => 'decimal:2',
        'fat_per_100g' => 'decimal:2',
        'calories_per_100g' => 'integer',
        'preparation_time' => 'integer',
        'display_order' => 'integer',
    ];

    protected $appends = ['primary_image_url', 'images_urls', 'seo'];

    /**
     * Generate a unique SKU based on category
     */
    public static function generateSKU($categoryId = null)
    {
        $prefix = $categoryId ? "CAT{$categoryId}" : 'PROD';

        // Get the last SKU for this category
        $lastProduct = self::where('sku', 'like', "{$prefix}-%")
            ->orderBy('sku', 'desc')
            ->first();

        if ($lastProduct) {
            // Extract the number from the last SKU
            $lastNumber = (int) mb_substr($lastProduct->sku, mb_strrpos($lastProduct->sku, '-') + 1);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Format: CAT5-000123
        $sku = sprintf('%s-%06d', $prefix, $newNumber);

        // Ensure uniqueness (in case of race conditions)
        while (self::where('sku', $sku)->exists()) {
            $newNumber++;
            $sku = sprintf('%s-%06d', $prefix, $newNumber);
        }

        return $sku;
    }

    /**
     * Manually regenerate SKU if needed
     */
    public function regenerateSKU()
    {
        $this->sku = self::generateSKU($this->category_id);
        $this->save();

        return $this->sku;
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Catalog-safe product visibility for API consumers.
     *
     * Supports the current enum status values and any legacy truthy status rows
     * without forcing a breaking data migration in Phase 1.
     */
    public function scopeVisibleForCatalog(Builder $query): Builder
    {
        return $query
            ->where('is_visible', true)
            ->where(function (Builder $builder) {
                $builder
                    ->where('status', 'active')
                    ->orWhere('status', '1')
                    ->orWhere('status', 1);
            });
    }

    public function getSeoCanonicalUrl(): string
    {
        return url('/product/'.$this->getKey());
    }

    /**
     * Get all product cuts for this product.
     */
    public function productCuts()
    {
        return $this->hasMany(ProductCut::class);
    }

    /**
     * Get active product cuts for this product.
     */
    public function activeProductCuts()
    {
        return $this->hasMany(ProductCut::class)->where('status', 1);
    }

    /**
     * Get popular product cuts for this product.
     */
    public function popularProductCuts()
    {
        return $this->hasMany(ProductCut::class)
            ->where('status', 1)
            ->where('popular', true)
            ->orderBy('display_order');
    }

    /**
     * Get customers who favorited this product.
     */
    public function favoritedByCustomers()
    {
        return $this->belongsToMany(Customer::class, 'customer_favorite_items')
            ->wherePivot('status', 1)
            ->withTimestamps();
    }

    /**
     * Get favorite items for this product.
     */
    public function favoriteItems()
    {
        return $this->hasMany(CustomerFavoriteItem::class);
    }

    /**
     * Get active favorite items for this product.
     */
    public function activeFavoriteItems()
    {
        return $this->hasMany(CustomerFavoriteItem::class)->where('status', 1);
    }

    /**
     * Get cart items for this product.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get active cart items for this product.
     */
    public function activeCartItems()
    {
        return $this->hasMany(CartItem::class)->where('status', 1);
    }

    /**
     * Get the count of customers who favorited this product.
     */
    public function getFavoritesCountAttribute(): int
    {
        return $this->activeFavoriteItems()->count();
    }

    /**
     * Check if this product is favorited by a specific customer.
     */
    public function isFavoritedBy($customerId): bool
    {
        return $this->favoriteItems()
            ->where('customer_id', $customerId)
            ->where('status', 1)
            ->exists();
    }

    /**
     * Get the lowest price among all cuts.
     */
    public function getLowestPriceAttribute(): float
    {
        $cutPrice = $this->activeProductCuts()->min('price_per_kg');

        return $cutPrice ?: $this->price;
    }

    /**
     * Get the highest price among all cuts.
     */
    public function getHighestPriceAttribute(): float
    {
        $cutPrice = $this->activeProductCuts()->max('price_per_kg');

        return $cutPrice ?: $this->price;
    }

    /**
     * Get price range display.
     */
    public function getPriceRangeAttribute(): string
    {
        $lowest = $this->lowest_price;
        $highest = $this->highest_price;

        if ($lowest === $highest) {
            return '₹'.number_format($lowest, 2);
        }

        return '₹'.number_format($lowest, 2).' - ₹'.number_format($highest, 2);
    }

    // public function brand()
    // {
    //     return $this->belongsTo(Brand::class);
    // }

    /**
     * The cut types that belong to the product.
     */
    public function cuttypes()
    {
        // Pivot table uses cuttype_id, not cut_type_id
        return $this->belongsToMany(CutType::class, 'cuttype_product', 'product_id', 'cuttype_id')
            ->withTimestamps();
    }

    public function getPrimaryImageUrlAttribute()
    {
        if (! $this->primary_image) {
            return null;
        }

        return Storage::disk('public')->url($this->primary_image);
    }

    public function getImagesUrlsAttribute()
    {
        if (! $this->images || ! is_array($this->images)) {
            return [];
        }

        return array_map(function ($path) {
            return Storage::disk('public')->url($path);
        }, $this->images);
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = self::generateSKU($product->category_id);
            }

            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }

            if (! isset($product->status) || $product->status === '' || $product->status === null) {
                $product->status = 'active';
            }

            if ($product->status === 1 || $product->status === '1' || $product->status === true) {
                $product->status = 'active';
            }

            if ($product->status === 0 || $product->status === '0' || $product->status === false) {
                $product->status = 'draft';
            }
        });

        self::created(function ($product) {
            $product->updateMeta([
                'meta_title' => $product->meta_title ?? $product->name,
                'meta_description' => $product->meta_description ?? $product->description,
                'og_title' => $product->name,
                'og_description' => $product->description,
                'og_image' => $product->primary_image,
                'canonical_url' => $product->getSeoCanonicalUrl(),
                'status' => true,
            ]);
        });
    }
}
