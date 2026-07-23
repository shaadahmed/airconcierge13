<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyPayment;
use App\Models\PropertyPaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyPayment>
 */
class PropertyPaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'property_payment_type_id' => PropertyPaymentType::factory(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'payment_date' => fake()->date(),
            'notes' => fake()->optional()->sentence(),
            'deleted' => false,
        ];
    }
}
