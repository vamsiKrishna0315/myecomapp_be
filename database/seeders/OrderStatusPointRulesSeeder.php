<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GamificationPointRule;
use Illuminate\Database\Seeder;

class OrderStatusPointRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Maps order status codes to reputation points
     */
    public function run(): void
    {
        $statusPointRules = [
            // Initial statuses - lower points
            [
                'event_type' => 'status_pending',
                'name' => 'Order Placed',
                'description' => 'Points when order status changes to Pending',
                'points' => 0,
                'is_active' => false, // Not awarding points for pending
                'conditions' => null,
            ],
            [
                'event_type' => 'status_confirmed',
                'name' => 'Order Confirmed',
                'description' => 'Points when vendor confirms the order',
                'points' => 15,
                'is_active' => true,
                'conditions' => null,
            ],
            [
                'event_type' => 'status_processing',
                'name' => 'Order Processing',
                'description' => 'Points when order is being processed',
                'points' => 10,
                'is_active' => true,
                'conditions' => null,
            ],
            [
                'event_type' => 'status_ready_for_pickup',
                'name' => 'Ready for Pickup',
                'description' => 'Points when order is ready for driver pickup',
                'points' => 20,
                'is_active' => true,
                'conditions' => null,
            ],
            
            // Driver-related statuses - medium points
            [
                'event_type' => 'status_assigned_to_driver',
                'name' => 'Assigned to Driver',
                'description' => 'Points when driver is assigned',
                'points' => 10,
                'is_active' => true,
                'conditions' => null,
            ],
            [
                'event_type' => 'status_driver_accepted',
                'name' => 'Driver Accepted',
                'description' => 'Points when driver accepts the delivery',
                'points' => 15,
                'is_active' => true,
                'conditions' => null,
            ],
            [
                'event_type' => 'status_driver_at_store',
                'name' => 'Driver at Store',
                'description' => 'Points when driver arrives at store',
                'points' => 10,
                'is_active' => true,
                'conditions' => null,
            ],
            [
                'event_type' => 'status_driver_picked_up',
                'name' => 'Order Picked Up',
                'description' => 'Points when driver picks up the order',
                'points' => 15,
                'is_active' => true,
                'conditions' => null,
            ],
            [
                'event_type' => 'status_driver_nearby',
                'name' => 'Driver Nearby',
                'description' => 'Points when driver is near customer',
                'points' => 10,
                'is_active' => true,
                'conditions' => null,
            ],
            [
                'event_type' => 'status_driver_reached',
                'name' => 'Driver Reached',
                'description' => 'Points when driver reaches customer',
                'points' => 10,
                'is_active' => true,
                'conditions' => null,
            ],
            
            // Final statuses - highest/lowest points
            [
                'event_type' => 'status_delivered',
                'name' => 'Order Delivered',
                'description' => 'Points when order is successfully delivered',
                'points' => 100,
                'is_active' => true,
                'conditions' => null,
            ],
            [
                'event_type' => 'status_cancelled',
                'name' => 'Order Cancelled',
                'description' => 'Points (penalty) when order is cancelled',
                'points' => 0,
                'is_active' => false, // Can be enabled with negative points if needed
                'conditions' => null,
            ],
            [
                'event_type' => 'status_returned',
                'name' => 'Order Returned',
                'description' => 'Points when order is returned',
                'points' => 0,
                'is_active' => false,
                'conditions' => null,
            ],
            [
                'event_type' => 'status_failed',
                'name' => 'Delivery Failed',
                'description' => 'Points when delivery fails',
                'points' => 0,
                'is_active' => false,
                'conditions' => null,
            ],
        ];

        foreach ($statusPointRules as $rule) {
            GamificationPointRule::updateOrCreate(
                ['event_type' => $rule['event_type']],
                $rule
            );
        }

        $this->command->info('Order status point rules seeded successfully!');
        $this->command->info('Total active rules: ' . GamificationPointRule::where('is_active', true)->where('event_type', 'like', 'status_%')->count());
    }
}
