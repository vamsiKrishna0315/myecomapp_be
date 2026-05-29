<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

final class CutType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'status',
        'show_live',
        'display_order',
    ];

    protected $casts = [
        'status' => 'integer',
        'show_live' => 'integer',
        'display_order' => 'integer',
    ];

    protected $appends = ['icon_url'];

    /**
     * The products that belong to the cut type.
     */
    public function products()
    {
        // Pivot table uses cuttype_id, not cut_type_id
        return $this->belongsToMany(Product::class, 'cuttype_product', 'cuttype_id', 'product_id')
            ->withTimestamps();
    }

    // Scope for active cut types
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // Scope for live cut types
    public function scopeLive($query)
    {
        return $query->where('show_live', 1);
    }

    public function getIconUrlAttribute()
    {
        $icon = $this->icon;
        if (! $icon) {
            return null;
        }
        if (str_starts_with($icon, 'http://') || str_starts_with($icon, 'https://') || str_starts_with($icon, 'data:')) {
            return $icon;
        }

        return Storage::disk('public')->url($icon);
    }
}
