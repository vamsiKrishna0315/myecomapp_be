<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderItems extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'category_id',
        'cut_id',
        'product_name',
        'cut_name',
        'sku',
        'price_per_kg',
        'price_per_piece',
        'ordered_weight',
        'actual_weight',
        'weight_unit',
        'line_subtotal',
        'line_discount',
        'line_tax',
        'line_total',
        'preparation_style',
        'is_cleaned',
        'is_skinless',
        'special_instructions',
        'order_item_status',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function cut(): BelongsTo
    {
        return $this->belongsTo(ProductCut::class, 'cut_id');
    }
}
