<?php

namespace Database\Factories;

use App\Models\HelloSignDetail;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HelloSignDetail>
 */
class HelloSignDetailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ownerid_id' => Owner::factory(),
            'ownerid_email' => fake()->safeEmail(),
            'zoho_request_id' => 'req_'.fake()->uuid(),
            'zoho_document_id' => 'doc_'.fake()->uuid(),
            'zoho_sign_status' => 0,
            'is_opened' => 0,
            'created_date' => now(),
        ];
    }
}
