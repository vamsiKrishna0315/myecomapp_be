<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class NotificationTemplate extends Model
{
    protected $fillable = [
        'channel',
        'provider',
        'name',
        'provider_template_name',
        'category',
        'language',
        'description',
        'status',
    ];

    public function eventMappings(): HasMany
    {
        return $this->hasMany(NotificationEventMapping::class);
    }
}
