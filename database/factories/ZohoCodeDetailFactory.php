<?php

namespace Database\Factories;

use App\Models\ZohoCodeDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ZohoCodeDetail>
 */
class ZohoCodeDetailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'zoho_code' => fake()->uuid(),
            'zoho_access_token' => 'access_'.fake()->sha256(),
            'zoho_refresh_token' => 'refresh_'.fake()->sha256(),
            'created_dtm' => now(),
            'update_dtm' => now(),
            'is_deleted' => 0,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'update_dtm' => now()->subHours(2),
        ]);
    }
}
