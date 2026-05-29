<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GamificationPointRule;
use Illuminate\Database\Seeder;

class GamificationPointRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            [
                'event_type' => 'order_created',
                'name' => 'Order Created',
                'description' => 'Points awarded when a vendor creates a new order',
                'points' => 10,
                'is_active' => true,
                'conditions' => null,
            ],
            [
                'event_type' => 'order_completed',
                'name' => 'Order Completed',
                'description' => 'Points awarded when an order status changes to completed',
                'points' => 50,
                'is_active' => true,
                'conditions' => null,
            ],
            [
                'event_type' => 'order_delivered',
                'name' => 'Order Delivered',
                'description' => 'Points awarded when an order is delivered to customer',
                'points' => 100,
                'is_active' => true,
                'conditions' => null,
            ],
            [
                'event_type' => 'high_value_order',
                'name' => 'High Value Order Bonus',
                'description' => 'Bonus points for orders over Rs. 5000 (1 point per Rs. 100)',
                'points' => 1,
                'is_active' => true,
                'conditions' => ['min_order_value' => 5000],
            ],
            [
                'event_type' => 'daily_streak',
                'name' => 'Daily Streak Bonus',
                'description' => 'Points awarded for consecutive days of activity',
                'points' => 25,
                'is_active' => false,
                'conditions' => null,
            ],
        ];

        foreach ($rules as $rule) {
            GamificationPointRule::updateOrCreate(
                ['event_type' => $rule['event_type']],
                $rule
            );
        }
    }
}
