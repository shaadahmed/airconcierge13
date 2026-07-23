<?php

namespace Database\Factories;

use App\Models\OutboundEmailLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OutboundEmailLog>
 */
class OutboundEmailLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => OutboundEmailLog::STATUS_PENDING,
            'subject' => fake()->sentence(4),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'to_addresses' => fake()->safeEmail(),
            'source' => 'test',
        ];
    }
}
