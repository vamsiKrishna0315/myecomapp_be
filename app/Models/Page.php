<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasMetaTag;
use Illuminate\Database\Eloquent\Model;

final class Page extends Model
{
    use HasMetaTag;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $appends = ['seo'];

    public function getSeoCanonicalUrl(): string
    {
        return url('/'.ltrim($this->slug, '/'));
    }
}
