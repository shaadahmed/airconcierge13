<?php

namespace Database\Factories;

use App\Models\Owner;
use App\Models\OwnerBlock;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OwnerBlock>
 */
class OwnerBlockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+1 month');

        return [
            'property_id' => Property::factory(),
            'owner_id' => Owner::factory(),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => (clone $start)->modify('+3 days')->format('Y-m-d'),
            'notes' => fake()->optional()->sentence(),
            'deleted' => false,
        ];
    }
}
