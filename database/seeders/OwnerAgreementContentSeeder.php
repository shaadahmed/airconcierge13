<?php

namespace Database\Seeders;

use App\Models\DynamicContent;
use Illuminate\Database\Seeder;

class OwnerAgreementContentSeeder extends Seeder
{
    public function run(): void
    {
        DynamicContent::query()->updateOrCreate(
            ['page_id' => DynamicContent::OWNER_AGREEMENT_PAGE_ID],
            [
                'content' => <<<'HTML'
<p>Welcome to Air Concierge. By accepting these terms, you agree to the owner agreement governing property management services, payouts, and platform use.</p>
<p>Please review the full terms carefully. Contact support if you have questions before accepting.</p>
HTML,
            ],
        );
    }
}
