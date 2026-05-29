<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ContactInquiry extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'phone',
        'email',
        'subject',
        'message',
        'inquiry_type',
        'status',
        'admin_response',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
