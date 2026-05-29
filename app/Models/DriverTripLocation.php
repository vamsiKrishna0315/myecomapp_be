<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DriverTripLocation extends Model
{
    protected $fillable = [
        'order_id',
        'driver_id',
        'lat',
        'lng',
        'accuracy_meters',
        'source_status_code',
        'trip_phase',
        'recorded_at',
        'status',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'accuracy_meters' => 'decimal:2',
        'recorded_at' => 'datetime',
        'status' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
}
