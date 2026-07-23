<?php

namespace Database\Factories;

use App\Enums\HostawayReservationLogStatus;
use App\Models\HostawayReservationLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostawayReservationLog>
 */
class HostawayReservationLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservation_id' => fake()->unique()->numberBetween(1000, 999999),
            'booking_id' => null,
            'booking_code' => fake()->bothify('??######'),
            'guest_name' => fake()->name(),
            'status' => HostawayReservationLogStatus::BookingCreateInProgress,
            'log_type' => HostawayReservationLog::LOG_TYPE_BOOKING,
            'comments' => null,
            'hostaway_response' => null,
            'difference_array' => null,
        ];
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => HostawayReservationLogStatus::Processed,
            'booking_id' => fake()->numberBetween(1, 9999),
        ]);
    }
}
