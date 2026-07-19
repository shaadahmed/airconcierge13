<?php

use App\Jobs\SyncHostawayReservationJob;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    config([
        'services.hostaway.webhook.username' => 'hostaway-user',
        'services.hostaway.webhook.password' => 'hostaway-secret',
    ]);
});

it('accepts the hostaway webhook with valid basic auth and dispatches the sync job', function (): void {
    Queue::fake();

    $payload = [
        'event' => 'reservation.created',
        'reservation' => ['id' => 123],
    ];

    $this->withBasicAuth('hostaway-user', 'hostaway-secret')
        ->postJson(route('webhooks.hostaway.booking.created'), $payload)
        ->assertOk();

    Queue::assertPushed(SyncHostawayReservationJob::class, function (SyncHostawayReservationJob $job) use ($payload): bool {
        return $job->payload === $payload;
    });
});

it('rejects the hostaway webhook with invalid basic auth without dispatching', function (): void {
    Queue::fake();

    $this->withBasicAuth('wrong-user', 'wrong-secret')
        ->postJson(route('webhooks.hostaway.booking.created'), ['event' => 'reservation.created'])
        ->assertUnauthorized();

    Queue::assertNothingPushed();
});

it('rejects the hostaway webhook when basic auth is missing without dispatching', function (): void {
    Queue::fake();

    $this->postJson(route('webhooks.hostaway.booking.created'), ['event' => 'reservation.created'])
        ->assertUnauthorized();

    Queue::assertNothingPushed();
});

it('rejects the hostaway webhook when webhook credentials are not configured', function (): void {
    Queue::fake();

    config([
        'services.hostaway.webhook.username' => '',
        'services.hostaway.webhook.password' => '',
    ]);

    $this->withBasicAuth('hostaway-user', 'hostaway-secret')
        ->postJson(route('webhooks.hostaway.booking.created'), ['event' => 'reservation.created'])
        ->assertUnauthorized();

    Queue::assertNothingPushed();
});
