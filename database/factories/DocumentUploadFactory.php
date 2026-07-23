<?php

namespace Database\Factories;

use App\Models\DocumentUpload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentUpload>
 */
class DocumentUploadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'ownerspecific' => 'no',
            'document' => fake()->slug().'.pdf',
            'roletitle' => 'Owner',
            'type' => 0,
            'createdate' => now(),
        ];
    }

    public function zoho(): static
    {
        return $this->state(fn (array $attributes): array => [
            'signid' => 'tmpl_'.fake()->uuid(),
            'zohoactionid' => 'act_'.fake()->uuid(),
            'type' => 1,
        ]);
    }
}
