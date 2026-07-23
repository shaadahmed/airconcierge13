<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Phase 2.2 stub — full notification delivery standards land in Phase 4.
 */
class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload = []) {}

    public function handle(): void
    {
        Log::info('SendNotificationJob stub executed.', $this->payload);
    }
}
