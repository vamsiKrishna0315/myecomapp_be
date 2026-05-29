<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CuttypeProduct extends Model
{
    protected $table = 'cuttype_product';

    protected $fillable = [
        'product_id',
        'cuttype_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cuttype(): BelongsTo
    {
        return $this->belongsTo(CutType::class, 'cuttype_id');
    }
}
