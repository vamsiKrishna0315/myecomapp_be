<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProofOfDelivery extends Model
{
    protected $table = 'proof_of_deliveries';

    protected $fillable = [
        'order_id',
        'driver_id',
        'order_status_id',
        'status_code',
        'image_path',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatuses::class, 'order_status_id');
    }
}
