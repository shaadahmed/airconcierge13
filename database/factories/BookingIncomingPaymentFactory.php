<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingIncomingPayment;
use App\Models\IncomingPaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingIncomingPayment>
 */
class BookingIncomingPaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'incoming_payment_type_id' => IncomingPaymentType::factory(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'payment_date' => fake()->date(),
            'notes' => fake()->optional()->sentence(),
            'deleted' => false,
        ];
    }
}
