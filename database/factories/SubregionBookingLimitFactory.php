<?php

namespace Database\Factories;

use App\Enums\BookingLimitType;
use App\Models\Subregion;
use App\Models\SubregionBookingLimit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubregionBookingLimit>
 */
class SubregionBookingLimitFactory extends Factory
{
    protected $model = SubregionBookingLimit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subregion_id' => Subregion::factory(),
            'limit_type' => fake()->randomElement(BookingLimitType::cases()),
            'limit_value' => fake()->numberBetween(1, 180),
            'limit_filter' => fake()->randomElement(['CALENDAR', 'PERMIT_YEAR']),
        ];
    }
}
