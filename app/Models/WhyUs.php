<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class WhyUs extends Model
{
    protected $fillable = [
        'title',
        'description',
        'year',
        'image',
        'show_live',
        'status',
    ];
}
