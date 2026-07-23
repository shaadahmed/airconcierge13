<?php

namespace Database\Factories;

use App\Models\OutboundEmailAttachment;
use App\Models\OutboundEmailLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OutboundEmailAttachment>
 */
class OutboundEmailAttachmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'outbound_email_log_id' => OutboundEmailLog::factory(),
            'original_filename' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'storage_type' => 'disk',
            'size_bytes' => 100,
        ];
    }
}
