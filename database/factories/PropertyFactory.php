<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Region;
use App\Models\Subregion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'region_id' => Region::factory(),
            'subregion_id' => Subregion::factory(),
            'hostaway_listing_id' => fake()->optional()->numberBetween(1000, 999999),
            'property_title' => fake()->streetAddress(),
            'email_title' => fake()->words(3, true),
            'street_address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zipcode' => fake()->postcode(),
            'status' => true,
            'management_type_id' => 1,
            'deleted' => false,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => false,
        ]);
    }

    public function live(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => true,
            'deleted' => false,
        ]);
    }
}
