<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamificationBadgeRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'level',
        'trigger_type',
        'threshold_value',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
        'threshold_value' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get all active badge rules ordered by sort order
     */
    public static function getActiveRules()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('threshold_value')
            ->get();
    }

    /**
     * Check if user qualifies for this badge
     */
    public function userQualifies(User $user): bool
    {
        if ($user->user_role !== 'store_vendor') {
            return false;
        }

        return match($this->trigger_type) {
            'order_count' => $this->checkOrderCount($user),
            'reputation_points' => $this->checkReputationPoints($user),
            default => false,
        };
    }

    /**
     * Check order count threshold
     */
    protected function checkOrderCount(User $user): bool
    {
        $orderCount = \App\Models\StoreVendorOrders::where('store_vendor_id', $user->id)->count();
        
        return $orderCount >= $this->threshold_value;
    }

    /**
     * Check reputation points threshold
     */
    protected function checkReputationPoints(User $user): bool
    {
        return $user->reputation >= $this->threshold_value;
    }
}
