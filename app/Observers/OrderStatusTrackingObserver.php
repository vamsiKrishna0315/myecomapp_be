<?php

declare(strict_types=1);

namespace App\Observers;

use App\Gamify\Points\OrderStatusPoint;
use App\Models\GamificationBadgeRule;
use App\Models\GamificationPointRule;
use App\Models\OrderStatusTracking;
use App\Models\StoreVendorOrders;
use App\Models\User;
use Log;

final class OrderStatusTrackingObserver
{
    /**
     * Handle the OrderStatusTracking "creating" event.
     * Auto-populate store_vendor_id from the order
     */
    public function creating(OrderStatusTracking $orderStatusTracking): void
    {
        // If store_vendor_id is not already set, try to populate it from the order
        if (empty($orderStatusTracking->store_vendor_id) && ! empty($orderStatusTracking->order_id)) {
            // Find the store vendor who created this order
            $storeVendorOrder = StoreVendorOrders::where('order_id', $orderStatusTracking->order_id)->first();

            if ($storeVendorOrder) {
                $orderStatusTracking->store_vendor_id = $storeVendorOrder->store_vendor_id;

                Log::info('OrderStatusTrackingObserver: Auto-populated store_vendor_id', [
                    'order_id' => $orderStatusTracking->order_id,
                    'store_vendor_id' => $storeVendorOrder->store_vendor_id,
                    'status_code' => $orderStatusTracking->status_code,
                ]);
            }
        }
    }

    /**
     * Handle the OrderStatusTracking "created" event.
     * Award reputation points to the vendor when status changes
     */
    public function created(OrderStatusTracking $orderStatusTracking): void
    {
        // Only proceed if we have a store_vendor_id
        if (empty($orderStatusTracking->store_vendor_id)) {
            Log::warning('OrderStatusTrackingObserver: No store_vendor_id found', [
                'tracking_id' => $orderStatusTracking->id,
                'order_id' => $orderStatusTracking->order_id,
            ]);

            return;
        }

        // Get the vendor
        $vendor = User::find($orderStatusTracking->store_vendor_id);

        if (! $vendor || $vendor->user_role !== 'store_vendor') {
            Log::warning('OrderStatusTrackingObserver: Invalid vendor', [
                'store_vendor_id' => $orderStatusTracking->store_vendor_id,
            ]);

            return;
        }

        // Check if there's a point rule for this status
        $eventType = 'status_'.$orderStatusTracking->status_code;
        $pointRule = GamificationPointRule::getActiveRule($eventType);

        if ($pointRule && $pointRule->points > 0) {
            // Award points for this status change
            $pointInstance = new OrderStatusPoint($orderStatusTracking, $orderStatusTracking->status_code);
            $vendor->givePoint($pointInstance);

            Log::info('OrderStatusTrackingObserver: Points awarded', [
                'vendor_id' => $vendor->id,
                'status_code' => $orderStatusTracking->status_code,
                'points' => $pointRule->points,
                'new_reputation' => $vendor->fresh()->reputation,
            ]);

            // Check and award badges
            $this->checkAndAwardBadges($vendor);
        } else {
            Log::debug('OrderStatusTrackingObserver: No points rule found or points = 0', [
                'status_code' => $orderStatusTracking->status_code,
                'event_type' => $eventType,
            ]);
        }
    }

    /**
     * Handle the OrderStatusTracking "updated" event.
     */
    public function updated(OrderStatusTracking $orderStatusTracking): void
    {
        //
    }

    /**
     * Handle the OrderStatusTracking "deleted" event.
     */
    public function deleted(OrderStatusTracking $orderStatusTracking): void
    {
        //
    }

    /**
     * Handle the OrderStatusTracking "restored" event.
     */
    public function restored(OrderStatusTracking $orderStatusTracking): void
    {
        //
    }

    /**
     * Handle the OrderStatusTracking "force deleted" event.
     */
    public function forceDeleted(OrderStatusTracking $orderStatusTracking): void
    {
        //
    }

    /**
     * Check and award badges to vendor based on achievements
     */
    private function checkAndAwardBadges(User $vendor): void
    {
        $badgeRules = GamificationBadgeRule::getActiveRules();

        foreach ($badgeRules as $badgeRule) {
            if ($badgeRule->userQualifies($vendor)) {
                $badgeModel = \QCod\Gamify\Badge::firstOrCreate([
                    'name' => $badgeRule->name,
                ], [
                    'description' => $badgeRule->description,
                    'icon' => $badgeRule->icon,
                    'level' => $badgeRule->level,
                ]);

                if (! $vendor->badges()->where('badge_id', $badgeModel->id)->exists()) {
                    $vendor->badges()->attach($badgeModel->id);

                    Log::info('OrderStatusTrackingObserver: Badge awarded', [
                        'vendor_id' => $vendor->id,
                        'badge_name' => $badgeRule->name,
                    ]);
                }
            }
        }
    }
}
