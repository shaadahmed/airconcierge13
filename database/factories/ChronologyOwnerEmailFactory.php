<?php

namespace Database\Factories;

use App\Models\Chronology;
use App\Models\ChronologyOwnerEmail;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChronologyOwnerEmail>
 */
class ChronologyOwnerEmailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chronology_id' => Chronology::factory(),
            'owner_id' => Owner::factory(),
            'owner_email' => fake()->safeEmail(),
            'status' => 1,
        ];
    }
}
