<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ComboItem extends Model
{
    protected $fillable = [
        'combo_id',
        'product_id',
        'cut_type_id',
        'weight',
        'weight_unit',
        'quantity',
        'item_price',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'item_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function cutType()
    {
        return $this->belongsTo(CutType::class);
    }
}
