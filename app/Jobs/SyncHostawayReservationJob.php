<?php

namespace App\Jobs;

use App\Services\Hostaway\HostawayReservationSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncHostawayReservationJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public array $payload,
    ) {}

    public function handle(HostawayReservationSyncService $sync): void
    {
        $sync->handle($this->payload);
    }
}
