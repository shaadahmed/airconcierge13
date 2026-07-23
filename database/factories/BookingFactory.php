<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Platform;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 month', '+1 month');
        $end = (clone $start)->modify('+'.fake()->numberBetween(1, 7).' days');

        return [
            'property_id' => Property::factory(),
            'region_id' => null,
            'subregion_id' => null,
            'platform_id' => Platform::factory(),
            'booking_code' => fake()->bothify('??######'),
            'hostaway_reservation_id' => fake()->unique()->numberBetween(1000, 999999),
            'reservation_start_date' => $start->format('Y-m-d'),
            'reservation_end_date' => $end->format('Y-m-d'),
            'booking_date' => now()->toDateString(),
            'no_of_guests' => fake()->numberBetween(1, 6),
            'no_of_nights' => (int) $start->diff($end)->days,
            'cancelled_booking' => false,
            'deleted' => false,
            'is_payment_clear' => true,
            'dateadded' => now(),
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'cancelled_booking' => true,
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'deleted' => true,
        ]);
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Booking $booking): void {
            if ($booking->property_id === null) {
                return;
            }

            $property = Property::query()->find($booking->property_id);

            if ($property === null) {
                return;
            }

            $booking->region_id ??= $property->region_id;
            $booking->subregion_id ??= $property->subregion_id;
        })->afterCreating(function (Booking $booking): void {
            if ($booking->region_id !== null && $booking->subregion_id !== null) {
                return;
            }

            $property = $booking->property;

            if ($property === null) {
                return;
            }

            $booking->forceFill([
                'region_id' => $booking->region_id ?? $property->region_id,
                'subregion_id' => $booking->subregion_id ?? $property->subregion_id,
            ])->save();
        });
    }
}
