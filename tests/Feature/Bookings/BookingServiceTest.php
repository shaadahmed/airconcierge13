<?php

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Owner;
use App\Models\OwnerEmailNotificationLog;
use App\Models\Property;
use App\Services\Bookings\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('creates a booking', function (): void {
    $property = Property::factory()->create();
    $guest = Guest::factory()->create();

    $booking = app(BookingService::class)->create([
        'property_id' => $property->id,
        'booking_code' => 'MANUAL-1',
        'reservation_start_date' => '2026-09-01',
        'reservation_end_date' => '2026-09-03',
        'guest_ids' => [$guest->id],
    ]);

    expect($booking->booking_code)->toBe('MANUAL-1')
        ->and($booking->guests)->toHaveCount(1)
        ->and($booking->isDeleted())->toBeFalse();
});

it('cancels a booking', function (): void {
    $booking = Booking::factory()->create();

    $cancelled = app(BookingService::class)->cancel($booking);

    expect($cancelled->isCancelled())->toBeTrue();
});

it('creates a booking from a hostaway reservation and notifies owners', function (): void {
    Mail::fake();

    $property = Property::factory()->create(['hostaway_listing_id' => 4242]);
    $owner = Owner::factory()->create(['emailstatus' => 1]);
    $property->owners()->attach($owner);

    $booking = app(BookingService::class)->createFromHostawayReservation([
        'id' => 8888,
        'confirmationCode' => 'HA-8888',
        'guestName' => 'Hostaway Guest',
        'guestEmail' => 'guest@example.com',
        'listingMapId' => 4242,
        'arrivalDate' => '2026-10-01',
        'departureDate' => '2026-10-04',
        'numberOfGuests' => 3,
        'status' => 'new',
    ]);

    expect($booking->hostaway_reservation_id)->toBe(8888)
        ->and($booking->booking_code)->toBe('HA-8888')
        ->and($booking->property_id)->toBe($property->id)
        ->and($booking->guests)->toHaveCount(1)
        ->and(OwnerEmailNotificationLog::query()->where('booking_id', $booking->id)->where('owner_id', $owner->id)->exists())->toBeTrue();
});
