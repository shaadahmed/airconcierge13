<?php

namespace Database\Factories;

use App\Models\IncomingPaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncomingPaymentType>
 */
class IncomingPaymentTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Platform Payout', 'Guest Payment', 'Deposit']),
            'deleted' => false,
        ];
    }
}
