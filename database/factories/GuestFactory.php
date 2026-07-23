<?php

namespace Database\Factories;

use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $first = fake()->firstName();
        $last = fake()->lastName();

        return [
            'guest_name' => "{$first} {$last}",
            'first_name' => $first,
            'last_name' => $last,
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'country' => fake()->country(),
            'blacklisted' => false,
            'notes' => null,
            'deleted' => false,
        ];
    }
}
