<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'region_name' => fake()->city().' Region',
            'shortcode' => fake()->unique()->lexify('???'),
            'color' => fake()->hexColor(),
            'deleted' => false,
        ];
    }
}
