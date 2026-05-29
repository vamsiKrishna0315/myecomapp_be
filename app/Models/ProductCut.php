<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

final class ProductCut extends Model
{
    protected $fillable = [
        'product_id',
        'cut_name',
        'cut_code',
        'price_per_kg',
        'price_per_piece',
        'minimum_weight',
        'weight_unit',
        'net_weight',
        'cut_type_id',
        'preparation_style',
        'product_grade_id',
        'is_cleaned',
        'is_skinless',
        'stock_quantity',
        'stock_unit',
        'status',
        'show_live',
        'requires_advance_order',
        'preparation_time',
        'image',
        'description',
        'popular',
        'display_order',
        'protein_per_100g',
        'fat_per_100g',
        'calories_per_100g',
    ];

    protected $casts = [
        'price_per_kg' => 'decimal:2',
        'price_per_piece' => 'decimal:2',
        'minimum_weight' => 'decimal:2',
        'net_weight' => 'decimal:2',
        'is_cleaned' => 'boolean',
        'is_skinless' => 'boolean',
        'stock_quantity' => 'decimal:2',
        'status' => 'integer',
        'show_live' => 'integer',
        'requires_advance_order' => 'boolean',
        'preparation_time' => 'integer',
        'popular' => 'boolean',
        'display_order' => 'integer',
        'protein_per_100g' => 'decimal:2',
        'fat_per_100g' => 'decimal:2',
        'calories_per_100g' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cutType(): BelongsTo
    {
        return $this->belongsTo(CutType::class);
    }

    public function productGrade(): BelongsTo
    {
        return $this->belongsTo(ProductGrade::class);
    }

    /**
     * Get all cart items for this product cut.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get active cart items for this product cut.
     */
    public function activeCartItems()
    {
        return $this->hasMany(CartItem::class)->where('status', 1);
    }

    /**
     * Check if this product cut is active.
     */
    public function isActive(): bool
    {
        return $this->status === 1;
    }

    /**
     * Check if this product cut is live/visible.
     */
    public function isLive(): bool
    {
        return $this->show_live === 1;
    }

    /**
     * Scope for active product cuts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for live/visible product cuts.
     */
    public function scopeLive($query)
    {
        return $query->where('show_live', 1);
    }

    /**
     * Scope for popular product cuts.
     */
    public function scopePopular($query)
    {
        return $query->where('popular', true);
    }

    /**
     * Get the display name with grade and cut type.
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->cut_name;

        if ($this->cutType) {
            $name .= ' ('.$this->cutType->name.')';
        }

        if ($this->productGrade) {
            $name .= ' - '.$this->productGrade->name;
        }

        return $name;
    }

    /**
     * Get formatted price per kg.
     */
    public function getFormattedPricePerKgAttribute(): string
    {
        return '₹'.number_format($this->price_per_kg, 2).'/kg';
    }

    /**
     * Get formatted price per piece.
     */
    public function getFormattedPricePerPieceAttribute(): string
    {
        return $this->price_per_piece
            ? '₹'.number_format($this->price_per_piece, 2).'/piece'
            : null;
    }

    public function getImageUrlAttribute()
    {
        if (! $this->image) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }
}
