<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'state_id' => State::factory(),
            'name' => fake()->city(),
            'code' => strtoupper(fake()->lexify('???')),
            'zip' => fake()->optional()->numberBetween(10000, 99999),
            'contact_code' => fake()->optional()->lexify('??'),
        ];
    }
}
