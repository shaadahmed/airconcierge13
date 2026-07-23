<?php

namespace Database\Factories;

use App\Models\HostawayAccessToken;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostawayAccessToken>
 */
class HostawayAccessTokenFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'access_token' => fake()->sha256(),
            'token_type' => 'Bearer',
            'expiry' => now()->addYear()->toDateString(),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expiry' => now()->subDay()->toDateString(),
        ]);
    }
}
