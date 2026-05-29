<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GamificationBadgeRule;
use Illuminate\Database\Seeder;

class GamificationBadgeRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'First Sale',
                'description' => 'Created your first order',
                'icon' => 'first-order',
                'level' => 1,
                'trigger_type' => 'order_count',
                'threshold_value' => 1,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Rising Star',
                'description' => 'Created 10 orders',
                'icon' => 'rising-star',
                'level' => 1,
                'trigger_type' => 'order_count',
                'threshold_value' => 10,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Sales Pro',
                'description' => 'Created 50 orders',
                'icon' => 'sales-pro',
                'level' => 2,
                'trigger_type' => 'order_count',
                'threshold_value' => 50,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Sales Champion',
                'description' => 'Created 100 orders - A true champion!',
                'icon' => 'sales-champion',
                'level' => 3,
                'trigger_type' => 'order_count',
                'threshold_value' => 100,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Point Master',
                'description' => 'Earned 500 reputation points',
                'icon' => 'point-master',
                'level' => 2,
                'trigger_type' => 'reputation_points',
                'threshold_value' => 500,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($badges as $badge) {
            GamificationBadgeRule::updateOrCreate(
                ['name' => $badge['name']],
                $badge
            );
        }
    }
}
