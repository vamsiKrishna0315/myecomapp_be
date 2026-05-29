<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Combo extends Model
{
    protected $fillable = [
        'name',
        'description',
        'total_price',
        'currency',
        'status',
        'is_visible',
        'display_order',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'status' => 'integer',
        'is_visible' => 'boolean',
        'display_order' => 'integer',
    ];

    public function comboItems()
    {
        return $this->hasMany(ComboItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'combo_items', 'combo_id', 'product_id')
            ->withPivot('cut_type_id', 'weight', 'weight_unit', 'quantity', 'item_price')
            ->withTimestamps();
    }
}
