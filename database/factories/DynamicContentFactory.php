<?php

namespace Database\Factories;

use App\Models\DynamicContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DynamicContent>
 */
class DynamicContentFactory extends Factory
{
    protected $model = DynamicContent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => DynamicContent::OWNER_AGREEMENT_PAGE_ID,
            'content' => '<p>Owner terms agreement content for testing.</p>',
        ];
    }
}
