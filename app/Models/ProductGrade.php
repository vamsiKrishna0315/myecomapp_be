<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductGrade extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_multiplier',
        'badge_color',
        'status',
        'show_live',
        'display_order'
    ];

    protected $casts = [
        'price_multiplier' => 'decimal:2',
        'status' => 'integer',
        'show_live' => 'integer',
        'display_order' => 'integer'
    ];

    public function productCuts(): HasMany
    {
        return $this->hasMany(ProductCut::class);
    }

    // Scope for active grades
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // Scope for live grades
    public function scopeLive($query)
    {
        return $query->where('show_live', 1);
    }

    // Helper method to get badge color classes
    public function getBadgeColorClass()
    {
        return match($this->badge_color) {
            'success' => 'bg-success text-white',
            'warning' => 'bg-warning text-dark',
            'danger' => 'bg-danger text-white',
            default => 'bg-secondary text-white'
        };
    }
}
