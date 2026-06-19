<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Media\MediaService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class FlashBanner extends Model
{
    use HasFactory;

    protected $appends = [
        'image_url',
    ];

    protected $fillable = [
        'name',
        'redirect_link',
        'image',
        'status',
        'is_live',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return app(MediaService::class)->publicUrl((string) $this->image);
    }
}
