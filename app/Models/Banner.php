<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Banners\BannerMediaService;
use Illuminate\Database\Eloquent\Model;

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
        return app(BannerMediaService::class)->bannerPathUrl($this->banner_path);
    }
}
