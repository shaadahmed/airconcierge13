<?php

namespace Database\Factories;

use App\Models\OwnerTermsAgreement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OwnerTermsAgreement>
 */
class OwnerTermsAgreementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->owner(),
            'agreed_terms' => true,
        ];
    }

    public function disagreed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'agreed_terms' => false,
        ]);
    }
}
