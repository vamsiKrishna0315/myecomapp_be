<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

final class Banner extends Model
{
    protected $appends = ['banner_path_url'];

    private $fillables =
        [
            'banner_name',
            'banner_path',
            'redirect_link',
            'show_live',
            'status',
            'created_at',
            'updated_at',
        ];

    public function getBannerPathUrlAttribute()
    {
        if (! $this->banner_path) {
            return null;
        }

        return Storage::disk('public')->url($this->banner_path);
    }
}
