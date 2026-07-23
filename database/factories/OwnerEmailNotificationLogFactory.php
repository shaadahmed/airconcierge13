<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Owner;
use App\Models\OwnerEmailNotificationLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OwnerEmailNotificationLog>
 */
class OwnerEmailNotificationLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'owner_id' => Owner::factory(),
            'email_type' => 'booking_created',
            'status' => 'sent',
        ];
    }
}
