<?php

declare(strict_types=1);

namespace App\Models;

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

    public function contactInfo()
    {
        return $this->hasOne(StoreContactInfo::class);
    }
}
