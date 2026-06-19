<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Media\MediaService;
use Illuminate\Database\Eloquent\Model;

final class Store extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address_1',
        'address_2',
        'city',
        'distict',
        'state',
        'pincode',
        'country',
        'total_address',
        'logo',
        'favicon',
        'website',
        'facebook',
        'instagram',
        'twitter',
        'linkedin',
        'youtube',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'location',
        'status',
    ];

    protected $appends = [
        'logo_url',
        'favicon_url',
    ];

    public function contactInfo()
    {
        return $this->hasOne(StoreContactInfo::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return app(MediaService::class)->publicUrl((string) $this->logo);
    }

    public function getFaviconUrlAttribute(): ?string
    {
        if (! $this->favicon) {
            return null;
        }

        return app(MediaService::class)->publicUrl((string) $this->favicon);
    }
}
