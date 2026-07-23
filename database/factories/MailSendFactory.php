<?php

namespace Database\Factories;

use App\Models\MailSend;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MailSend>
 */
class MailSendFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => Owner::factory(),
            'is_opened' => 0,
            'created_date' => now(),
        ];
    }
}
