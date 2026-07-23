<?php

use App\Enums\HostawayReservationLogStatus;
use App\Jobs\SyncHostawayReservationJob;
use App\Models\HostawayReservationLog;
use App\Services\Hostaway\HostawayReservationSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('writes a deferred create log for a new reservation webhook payload', function (): void {
    $payload = [
        'id' => 12345,
        'status' => 'new',
        'confirmationCode' => 'ABC123',
        'guestName' => 'Jane Guest',
    ];

    (new SyncHostawayReservationJob($payload))->handle(app(HostawayReservationSyncService::class));

    $log = HostawayReservationLog::query()->forReservation(12345)->first();

    expect($log)->not->toBeNull()
        ->and($log->status)->toBe(HostawayReservationLogStatus::BookingCreateInProgress)
        ->and($log->booking_code)->toBe('ABC123')
        ->and($log->guest_name)->toBe('Jane Guest')
        ->and($log->comments)->toBe(HostawayReservationSyncService::DEFERRED_COMMENT)
        ->and($log->isSuccessfullyProcessed())->toBeFalse();
});

it('is idempotent when the same new reservation is delivered twice', function (): void {
    $payload = [
        'id' => 555,
        'status' => 'new',
        'guestName' => 'Repeat Guest',
    ];

    $sync = app(HostawayReservationSyncService::class);

    (new SyncHostawayReservationJob($payload))->handle($sync);
    (new SyncHostawayReservationJob($payload))->handle($sync);

    expect(HostawayReservationLog::query()->forReservation(555)->count())->toBe(1);
});

it('skips create when the reservation was already processed', function (): void {
    HostawayReservationLog::factory()->processed()->create([
        'reservation_id' => 777,
        'booking_code' => 'EXISTING',
    ]);

    (new SyncHostawayReservationJob([
        'id' => 777,
        'status' => 'new',
        'guestName' => 'Should Skip',
    ]))->handle(app(HostawayReservationSyncService::class));

    $log = HostawayReservationLog::query()->forReservation(777)->first();

    expect($log->status)->toBe(HostawayReservationLogStatus::Processed)
        ->and($log->booking_code)->toBe('EXISTING')
        ->and(HostawayReservationLog::query()->count())->toBe(1);
});

it('stubs modified and cancelled reservations without marking success', function (): void {
    $sync = app(HostawayReservationSyncService::class);

    $sync->handle([
        'id' => 101,
        'status' => 'modified',
        'guestName' => 'Mod Guest',
    ]);

    $sync->handle([
        'id' => 202,
        'status' => 'cancelled',
        'guestName' => 'Cancel Guest',
    ]);

    expect(HostawayReservationLog::query()->forReservation(101)->first()->status)
        ->toBe(HostawayReservationLogStatus::BookingUpdateInProgress)
        ->and(HostawayReservationLog::query()->forReservation(202)->first()->status)
        ->toBe(HostawayReservationLogStatus::CancellationInProgress);
});

it('stubs pending paid reservations as creates', function (): void {
    app(HostawayReservationSyncService::class)->handle([
        'id' => 303,
        'status' => 'pending',
        'paymentStatus' => 'paid',
        'guestName' => 'Pending Paid',
    ]);

    expect(HostawayReservationLog::query()->forReservation(303)->first()->status)
        ->toBe(HostawayReservationLogStatus::BookingCreateInProgress);
});

it('dispatches the sync job from the webhook and the job persists a log', function (): void {
    config([
        'services.hostaway.webhook.username' => 'hostaway-user',
        'services.hostaway.webhook.password' => 'hostaway-secret',
    ]);

    $payload = [
        'id' => 9991,
        'status' => 'new',
        'guestName' => 'Webhook Guest',
    ];

    Queue::fake();

    $this->withBasicAuth('hostaway-user', 'hostaway-secret')
        ->postJson(route('webhooks.hostaway.booking.created'), $payload)
        ->assertOk();

    Queue::assertPushed(SyncHostawayReservationJob::class);

    (new SyncHostawayReservationJob($payload))->handle(app(HostawayReservationSyncService::class));

    expect(HostawayReservationLog::query()->forReservation(9991)->exists())->toBeTrue();
});
