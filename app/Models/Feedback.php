<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $fillable = [
        'description',
        'rating',
        'status',
        'show_live',
        // 'customer_id' will be added later when customers table is created
    ];

    protected $casts = [
        'rating' => 'integer',
        'status' => 'boolean',
        'show_live' => 'boolean'
    ];

    /**
     * Get the customer that owns the feedback.
     * TODO: Uncomment when customers table is created
     */
    /*
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    */
}
