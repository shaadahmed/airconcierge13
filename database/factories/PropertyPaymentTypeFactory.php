<?php

namespace Database\Factories;

use App\Models\PropertyPaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyPaymentType>
 */
class PropertyPaymentTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Maintenance', 'Supplies', 'Utilities']),
            'deleted' => false,
        ];
    }
}
