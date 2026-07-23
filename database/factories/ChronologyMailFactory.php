<?php

namespace Database\Factories;

use App\Models\Chronology;
use App\Models\ChronologyMail;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChronologyMail>
 */
class ChronologyMailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chronology_id' => Chronology::factory(),
            'order_id' => null,
            'owner_id' => Owner::factory(),
            'is_opened' => '0',
            'created_on' => now(),
        ];
    }
}
