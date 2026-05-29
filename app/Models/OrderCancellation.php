<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCancellation extends Model
{
    protected $fillable = [
        'order_id',
        'cancelled_by',
        'cancelled_by_id',
        'reason_code',
        'reason_description',
        'refund_amount',
        'refund_status',
        'cancelled_at',
        'refund_processed_at',
        'processed_by',
        'admin_notes',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }
}
