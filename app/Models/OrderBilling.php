<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderBilling extends Model
{
    protected $fillable = [
        'order_id',
        'billing_type_id',
        'amount',
        'transaction_id',
        'payment_gateway',
        'payment_response',
        'status',
        'billing_status',
        'paid_at',
        'refund_amount',
        'refund_transaction_id',
        'refunded_at',
        'refund_reason',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function billingType()
    {
        return $this->belongsTo(BillingType::class, 'billing_type_id');
    }
}
