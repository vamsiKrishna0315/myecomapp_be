<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FlashBanner>
 */
final class FlashBannerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'image' => 'flash-banners/'.fake()->uuid().'.jpg',
            'redirect_link' => fake()->url(),
            'is_live' => true,
            'status' => true,
        ];
    }

    public function offline(): static
    {
        return $this->state(['is_live' => false, 'status' => false]);
    }
}
