<?php

use App\Models\Booking;
use App\Models\Property;
use App\Services\Alerts\AlertDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('detects overlapping booking conflicts', function (): void {
    Log::spy();

    $property = Property::factory()->live()->create();
    Booking::factory()->create([
        'property_id' => $property->id,
        'reservation_start_date' => now()->toDateString(),
        'reservation_end_date' => now()->addDays(5)->toDateString(),
        'cancelled_booking' => false,
    ]);
    Booking::factory()->create([
        'property_id' => $property->id,
        'reservation_start_date' => now()->addDays(2)->toDateString(),
        'reservation_end_date' => now()->addDays(7)->toDateString(),
        'cancelled_booking' => false,
    ]);

    $count = app(AlertDispatchService::class)->detectBookingConflicts();

    expect($count)->toBeGreaterThanOrEqual(1);
});

it('detects booking month differences for current year', function (): void {
    Log::spy();

    Booking::factory()->create([
        'month' => 1,
        'year' => (int) date('Y'),
        'reservation_start_date' => now()->month(6)->startOfMonth()->toDateString(),
        'cancelled_booking' => false,
    ]);

    $count = app(AlertDispatchService::class)->detectBookingMonthDifferences();

    expect($count)->toBeGreaterThanOrEqual(1);
});

it('logs deferral for schema-missing alert keys', function (): void {
    Log::spy();

    app(AlertDispatchService::class)->run('owners-payout');

    Log::shouldHaveReceived('info')
        ->withArgs(fn (string $message, array $context = []): bool => $message === 'AlertDispatchService deferred alert key.'
            && ($context['alert_key'] ?? null) === 'owners-payout')
        ->once();
});

it('runs property vacancy detection without error', function (): void {
    Property::factory()->live()->create([
        'contract_start_date' => now()->subMonth()->toDateString(),
        'contract_end_date' => now()->addYear()->toDateString(),
    ]);

    $count = app(AlertDispatchService::class)->detectPropertyVacancies();

    expect($count)->toBeGreaterThanOrEqual(1);
});
