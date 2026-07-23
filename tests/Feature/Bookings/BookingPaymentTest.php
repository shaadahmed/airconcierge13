<?php

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use App\Services\Bookings\BookingService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('creates and cancels bookings through BookingService', function (): void {
    $property = Property::factory()->live()->create();

    $booking = app(BookingService::class)->create([
        'property_id' => $property->id,
        'region_id' => $property->region_id,
        'booking_code' => 'TEST-1',
        'reservation_start_date' => now()->toDateString(),
        'reservation_end_date' => now()->addDays(2)->toDateString(),
    ]);

    expect($booking->booking_code)->toBe('TEST-1');

    $cancelled = app(BookingService::class)->cancel($booking);

    expect($cancelled->cancelled_booking)->toBeTrue();
});

it('records booking payments through PaymentService', function (): void {
    $booking = Booking::factory()->create();

    $payment = app(PaymentService::class)->createBookingPayment([
        'booking_id' => $booking->id,
        'amount' => 100.50,
        'payment_date' => now()->toDateString(),
    ]);

    $receipt = app(PaymentService::class)->recordReceiptPath($payment, 'receipts/test.pdf');

    expect((float) $payment->amount)->toBe(100.50)
        ->and($receipt->receipt_path)->toBe('receipts/test.pdf');
});

it('allows staff to create bookings over http', function (): void {
    Mail::fake();
    $admin = User::factory()->admin()->create();
    $property = Property::factory()->live()->create();

    $this->actingAs($admin)
        ->postJson(route('admin.bookings.store'), [
            'property_id' => $property->id,
            'booking_code' => 'HTTP-1',
            'reservation_start_date' => now()->toDateString(),
            'reservation_end_date' => now()->addDay()->toDateString(),
        ])
        ->assertCreated()
        ->assertJsonPath('data.booking_code', 'HTTP-1');
});

it('forbids owners from listing bookings', function (): void {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)
        ->getJson(route('admin.bookings.index'))
        ->assertForbidden();
});
