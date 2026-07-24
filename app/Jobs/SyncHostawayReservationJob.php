<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesJobFailures;
use App\Services\Hostaway\HostawayReservationSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncHostawayReservationJob implements ShouldQueue
{
    use HandlesJobFailures;
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public array $payload,
    ) {}

    public function handle(HostawayReservationSyncService $sync): void
    {
        $reservationId = $this->payload['reservationId']
            ?? $this->payload['id']
            ?? null;

        Log::info('SyncHostawayReservationJob processing webhook payload.', $this->jobLogContext([
            'entity_id' => $reservationId,
        ]));

        $sync->handle($this->payload);
    }
}
