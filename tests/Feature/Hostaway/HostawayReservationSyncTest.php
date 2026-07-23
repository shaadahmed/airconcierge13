<?php

use App\Enums\HostawayReservationLogStatus;
use App\Jobs\SyncHostawayReservationJob;
use App\Models\Booking;
use App\Models\HostawayReservationLog;
use App\Models\Owner;
use App\Models\OwnerEmailNotificationLog;
use App\Models\Property;
use App\Services\Bookings\BookingService;
use App\Services\Hostaway\HostawayReservationSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('creates a booking from a new reservation webhook payload', function (): void {
    Mail::fake();

    $property = Property::factory()->create(['hostaway_listing_id' => 9001]);
    $owner = Owner::factory()->create(['emailstatus' => 1]);
    $property->owners()->attach($owner);

    $payload = [
        'id' => 12345,
        'status' => 'new',
        'confirmationCode' => 'ABC123',
        'guestName' => 'Jane Guest',
        'guestEmail' => 'jane@example.com',
        'listingMapId' => 9001,
        'arrivalDate' => '2026-08-01',
        'departureDate' => '2026-08-05',
        'numberOfGuests' => 2,
    ];

    (new SyncHostawayReservationJob($payload))->handle(app(HostawayReservationSyncService::class));

    $log = HostawayReservationLog::query()->forReservation(12345)->first();
    $booking = Booking::query()->where('hostaway_reservation_id', 12345)->first();

    expect($booking)->not->toBeNull()
        ->and($booking->booking_code)->toBe('ABC123')
        ->and($booking->property_id)->toBe($property->id)
        ->and($log)->not->toBeNull()
        ->and($log->status)->toBe(HostawayReservationLogStatus::Processed)
        ->and($log->booking_id)->toBe($booking->id)
        ->and($log->comments)->toBeNull()
        ->and($log->isSuccessfullyProcessed())->toBeTrue()
        ->and(OwnerEmailNotificationLog::query()->where('booking_id', $booking->id)->exists())->toBeTrue();
});

it('is idempotent when the same new reservation is delivered twice', function (): void {
    Mail::fake();

    Property::factory()->create(['hostaway_listing_id' => 5550]);

    $payload = [
        'id' => 555,
        'status' => 'new',
        'guestName' => 'Repeat Guest',
        'listingMapId' => 5550,
    ];

    $sync = app(HostawayReservationSyncService::class);

    (new SyncHostawayReservationJob($payload))->handle($sync);
    (new SyncHostawayReservationJob($payload))->handle($sync);

    expect(HostawayReservationLog::query()->forReservation(555)->count())->toBe(1)
        ->and(Booking::query()->where('hostaway_reservation_id', 555)->count())->toBe(1);
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
        'listingMapId' => 1,
    ]))->handle(app(HostawayReservationSyncService::class));

    $log = HostawayReservationLog::query()->forReservation(777)->first();

    expect($log->status)->toBe(HostawayReservationLogStatus::Processed)
        ->and($log->booking_code)->toBe('EXISTING')
        ->and(HostawayReservationLog::query()->count())->toBe(1);
});

it('updates and cancels reservations with success statuses', function (): void {
    Mail::fake();

    Property::factory()->create(['hostaway_listing_id' => 1010]);

    $sync = app(HostawayReservationSyncService::class);

    HostawayReservationLog::factory()->create([
        'reservation_id' => 101,
        'status' => HostawayReservationLogStatus::Failed,
        'booking_code' => 'MOD101',
    ]);

    $sync->handle([
        'id' => 101,
        'status' => 'modified',
        'guestName' => 'Mod Guest Updated',
        'listingMapId' => 1010,
        'confirmationCode' => 'MOD101B',
    ]);

    expect(HostawayReservationLog::query()->forReservation(101)->first()->status)
        ->toBe(HostawayReservationLogStatus::BookingUpdateSuccess)
        ->and(Booking::query()->where('hostaway_reservation_id', 101)->value('booking_code'))
        ->toBe('MOD101B');

    Property::factory()->create(['hostaway_listing_id' => 2020]);

    $sync->handle([
        'id' => 202,
        'status' => 'cancelled',
        'guestName' => 'Cancel Guest',
        'listingMapId' => 2020,
        'confirmationCode' => 'CAN202',
    ]);

    $cancelLog = HostawayReservationLog::query()->forReservation(202)->first();
    $cancelled = Booking::query()->where('hostaway_reservation_id', 202)->first();

    expect($cancelLog->status)->toBe(HostawayReservationLogStatus::Cancelled)
        ->and($cancelled->isCancelled())->toBeTrue();
});

it('creates pending paid reservations as bookings', function (): void {
    Mail::fake();

    Property::factory()->create(['hostaway_listing_id' => 3030]);

    app(HostawayReservationSyncService::class)->handle([
        'id' => 303,
        'status' => 'pending',
        'paymentStatus' => 'paid',
        'guestName' => 'Pending Paid',
        'listingMapId' => 3030,
    ]);

    expect(HostawayReservationLog::query()->forReservation(303)->first()->status)
        ->toBe(HostawayReservationLogStatus::Processed)
        ->and(Booking::query()->where('hostaway_reservation_id', 303)->exists())->toBeTrue();
});

it('dispatches the sync job from the webhook and the job persists a log', function (): void {
    Mail::fake();

    Property::factory()->create(['hostaway_listing_id' => 99910]);

    config([
        'services.hostaway.webhook.username' => 'hostaway-user',
        'services.hostaway.webhook.password' => 'hostaway-secret',
    ]);

    $payload = [
        'id' => 9991,
        'status' => 'new',
        'guestName' => 'Webhook Guest',
        'listingMapId' => 99910,
    ];

    Queue::fake();

    $this->withBasicAuth('hostaway-user', 'hostaway-secret')
        ->postJson(route('webhooks.hostaway.booking.created'), $payload)
        ->assertOk();

    Queue::assertPushed(SyncHostawayReservationJob::class);

    (new SyncHostawayReservationJob($payload))->handle(app(HostawayReservationSyncService::class));

    expect(HostawayReservationLog::query()->forReservation(9991)->exists())->toBeTrue()
        ->and(app(BookingService::class))->toBeInstanceOf(BookingService::class);
});
