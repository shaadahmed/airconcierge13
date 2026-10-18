<?php

namespace Database\Factories;

use App\Models\Region;
use App\Models\Subregion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subregion>
 */
class SubregionFactory extends Factory
{
    protected $model = Subregion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'region_id' => Region::factory(),
            'name' => fake()->unique()->city(),
            'transient_occupancy_tax' => fake()->randomFloat(2, 0, 20),
            'short_stay_len' => fake()->numberBetween(1, 31),
        ];
    }
}
