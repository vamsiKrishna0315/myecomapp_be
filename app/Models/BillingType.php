<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingType extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'icon',
        'description',
        'display_order',
        'status',
    ];

    public function orderBillings()
    {
        return $this->hasMany(OrderBilling::class, 'billing_type_id');
    }
}
