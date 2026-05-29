<?php

namespace App\Observers;

use App\Models\StoreVendorOrders;
use App\Models\User;
use App\Models\GamificationBadgeRule;
use App\Gamify\Points\OrderCreatedPoint;
use App\Gamify\Points\OrderCompletedPoint;
use App\Gamify\Points\HighValueOrderPoint;

class StoreVendorOrdersObserver
{
    /**
     * Handle the StoreVendorOrders "created" event.
     */
    public function created(StoreVendorOrders $storeVendorOrders): void
    {
        \Log::info('StoreVendorOrdersObserver: created event', [
            'id' => $storeVendorOrders->id,
            'order_id' => $storeVendorOrders->order_id,
            'customer_id' => $storeVendorOrders->customer_id,
            'store_id' => $storeVendorOrders->store_id,
            'store_vendor_id' => $storeVendorOrders->store_vendor_id,
            'is_eligible' => $storeVendorOrders->is_eligible,
            'status' => $storeVendorOrders->status,
        ]);

        // Award points for creating an order
        $vendor = User::find($storeVendorOrders->store_vendor_id);
        if ($vendor && $vendor->user_role === 'store_vendor') {
            // Award basic points for order creation
            $vendor->givePoint(new OrderCreatedPoint($storeVendorOrders));

            // Check and award badges manually
            $this->checkAndAwardBadges($vendor);
        }
    }

    /**
     * Check and award badges to vendor based on achievements (using database rules)
     */
    private function checkAndAwardBadges(User $vendor): void
    {
        // Get all active badge rules from database
        $badgeRules = GamificationBadgeRule::getActiveRules();

        foreach ($badgeRules as $badgeRule) {
            // Check if vendor qualifies for this badge
            if ($badgeRule->userQualifies($vendor)) {
                // Get or create the badge in the badges table
                $badgeModel = \QCod\Gamify\Badge::firstOrCreate([
                    'name' => $badgeRule->name,
                ], [
                    'description' => $badgeRule->description,
                    'icon' => $badgeRule->icon,
                    'level' => $badgeRule->level,
                ]);

                // Award badge if not already awarded
                if (!$vendor->badges()->where('badge_id', $badgeModel->id)->exists()) {
                    $vendor->badges()->attach($badgeModel->id);
                }
            }
        }
    }

    /**
     * Handle the StoreVendorOrders "updated" event.
     */
    public function updated(StoreVendorOrders $storeVendorOrders): void
    {
        // Check if order was completed (status changed to completed)
        if ($storeVendorOrders->isDirty('status') && $storeVendorOrders->status === 1) {
            $vendor = User::find($storeVendorOrders->store_vendor_id);
            if ($vendor && $vendor->user_role === 'store_vendor') {
                // Award points for completing an order
                $vendor->givePoint(new OrderCompletedPoint($storeVendorOrders));

                // Check if this is a high-value order and award bonus points
                $order = $storeVendorOrders->order;
                if ($order && $order->total_amount >= 5000) {
                    // Award bonus points based on order value
                    $bonusPoints = (int) ($order->total_amount / 100);
                    $vendor->givePoint(new HighValueOrderPoint($storeVendorOrders, $bonusPoints));
                }

                // Re-sync badges after completion
                $this->checkAndAwardBadges($vendor);
            }
        }
    }

    /**
     * Handle the StoreVendorOrders "deleted" event.
     */
    public function deleted(StoreVendorOrders $storeVendorOrders): void
    {
        //
    }

    /**
     * Handle the StoreVendorOrders "restored" event.
     */
    public function restored(StoreVendorOrders $storeVendorOrders): void
    {
        //
    }

    /**
     * Handle the StoreVendorOrders "force deleted" event.
     */
    public function forceDeleted(StoreVendorOrders $storeVendorOrders): void
    {
        //
    }
}
