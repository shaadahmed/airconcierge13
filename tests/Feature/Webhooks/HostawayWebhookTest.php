<?php

use App\Jobs\SyncHostawayReservationJob;
use Illuminate\Support\Facades\Queue;

it('accepts the hostaway webhook and dispatches the sync job', function (): void {
    Queue::fake();

    $payload = [
        'event' => 'reservation.created',
        'reservation' => ['id' => 123],
    ];

    $this->postJson(route('webhooks.hostaway.booking.created'), $payload)
        ->assertOk();

    Queue::assertPushed(SyncHostawayReservationJob::class, function (SyncHostawayReservationJob $job) use ($payload): bool {
        return $job->payload === $payload;
    });
});
