<?php

namespace Database\Factories;

use App\Models\Owner;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Owner>
 */
class OwnerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $first = fake()->firstName();
        $last = fake()->lastName();

        return [
            'region_id' => Region::factory(),
            'full_name' => "{$first} {$last}",
            'first_name' => $first,
            'last_name' => $last,
            'owner_phone' => fake()->phoneNumber(),
            'owner_email' => fake()->unique()->safeEmail(),
            'payment_method' => 'Direct Deposit',
            'w9_on_file' => false,
            'emailstatus' => 1,
            'deleted' => false,
        ];
    }
}
