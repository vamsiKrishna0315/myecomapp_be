<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class FlashBanner extends Model
{
    protected $fillable = [
        'name',
        'redirect_link',
        'image',
        'status',
        'is_live',
    ];
}
