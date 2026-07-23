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
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'region_id' => Region::factory(),
            'subregion_name' => fake()->streetName(),
            'airbnb_tot_region' => false,
            'transient_occupancy_tax' => fake()->randomFloat(2, 0, 15),
            'deleted' => false,
        ];
    }
}
