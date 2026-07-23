<?php

namespace Database\Factories;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Platform>
 */
class PlatformFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'platform_name' => fake()->randomElement(['Airbnb', 'VRBO', 'Booking.com', 'Direct']),
            'deleted' => false,
        ];
    }
}
