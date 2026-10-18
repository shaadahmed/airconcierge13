<?php

namespace Database\Factories;

use App\Models\Subregion;
use App\Models\SubregionPermit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubregionPermit>
 */
class SubregionPermitFactory extends Factory
{
    protected $model = SubregionPermit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issue = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'subregion_id' => Subregion::factory(),
            'business_license_account' => fake()->randomElement([0, 1]),
            'permit_no' => strtoupper(fake()->bothify('??-###')),
            'permit_issue_date' => $issue->format('Y-m-d'),
            'permit_length' => 12,
            'permit_expiry_date' => (clone $issue)->modify('+12 months')->format('Y-m-d'),
        ];
    }
}
