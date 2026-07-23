<?php

namespace Database\Factories;

use App\Models\Chronology;
use App\Models\ChronologyOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChronologyOrder>
 */
class ChronologyOrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chronology_id' => Chronology::factory(),
            'day' => 0,
            'time' => '00',
            'minute' => '00',
            'previousaction' => '0',
            'created_date' => now(),
            'update_date' => now(),
        ];
    }
}
