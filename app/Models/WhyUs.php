<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Media\MediaService;
use Illuminate\Database\Eloquent\Model;

final class WhyUs extends Model
{
    protected $appends = [
        'image_url',
    ];

    protected $fillable = [
        'title',
        'description',
        'year',
        'image',
        'show_live',
        'status',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return app(MediaService::class)->publicUrl((string) $this->image);
    }
}
