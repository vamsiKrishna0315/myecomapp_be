<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class StoreContactInfo extends Model
{
    protected $fillable = [
        'store_id',
        'phone',
        'whatsapp_number',
        'email',
        'bulk_order_email',
        'partnership_email',
        'business_hours',
        'whatsapp_message',
        'status',
        'show_live',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
