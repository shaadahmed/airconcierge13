<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\PaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingPayment>
 */
class BookingPaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'payment_type_id' => PaymentType::factory(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'payment_date' => fake()->date(),
            'notes' => fake()->optional()->sentence(),
            'deleted' => false,
        ];
    }
}
