<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingAlert>
 */
class BookingAlertFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'alert_type' => fake()->randomElement(['conflict', 'gap', 'deposit']),
            'message' => fake()->sentence(),
            'resolved' => false,
        ];
    }
}
