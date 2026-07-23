<?php

namespace Database\Factories;

use App\Models\PropertyPaymentType;
use App\Models\PropertyPaymentTypeFee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyPaymentTypeFee>
 */
class PropertyPaymentTypeFeeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_payment_type_id' => PropertyPaymentType::factory(),
            'fee_amount' => fake()->randomFloat(2, 1, 50),
            'fee_label' => fake()->words(2, true),
        ];
    }
}
