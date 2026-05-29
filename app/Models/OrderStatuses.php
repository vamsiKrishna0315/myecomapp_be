<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class OrderStatuses extends Model
{
    protected $fillable = [
        'code',
        'name',
        'color',
        'description',
        'sequence',
        'is_final',
        'is_cancellable',
        'display_order',
        'status',
    ];

    // add negative status codes function
    public static function negativeStatusCodes()
    {
        return ['failed', 'returned', 'cancelled', 'deleted'];
    }

    public function orders()
    {
        return $this->hasMany(Orders::class, 'current_status_id');
    }

    public function statusTracking()
    {
        return $this->hasMany(OrderStatusTracking::class, 'order_status_id');
    }
}
