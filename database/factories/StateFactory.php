<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<State>
 */
class StateFactory extends Factory
{
    protected $model = State::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'code' => strtoupper(fake()->lexify('??')),
            'name' => fake()->state(),
            'lat' => fake()->latitude(),
            'lng' => fake()->longitude(),
        ];
    }
}
