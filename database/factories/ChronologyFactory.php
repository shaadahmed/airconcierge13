<?php

namespace Database\Factories;

use App\Models\Chronology;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chronology>
 */
class ChronologyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'startdate' => now()->subDays(30)->toDateString(),
            'allregion' => 0,
            'allsubregion' => 0,
            'chronologyoption' => 0,
            'created_date' => now(),
            'update_date' => now(),
        ];
    }
}
