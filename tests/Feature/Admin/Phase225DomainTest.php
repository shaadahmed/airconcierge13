<?php

use App\Models\Booking;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use App\Services\Documents\DocumentService;
use App\Services\Import\ImportService;
use App\Services\Properties\PropertyService;
use App\Services\Reports\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns dashboard summary stats', function (): void {
    Property::factory()->live()->create();
    Booking::factory()->create([
        'reservation_start_date' => now()->toDateString(),
        'total_guest_paid' => 250,
        'cancelled_booking' => false,
    ]);

    $stats = app(DashboardService::class)->summaryStats();

    expect($stats['active_properties'])->toBeGreaterThanOrEqual(1)
        ->and($stats['bookings_this_month'])->toBeGreaterThanOrEqual(1);
});

it('aggregates booking summary reports', function (): void {
    $property = Property::factory()->live()->create();
    Booking::factory()->create([
        'property_id' => $property->id,
        'total_guest_paid' => 100,
        'cancelled_booking' => false,
    ]);

    $summary = app(ReportService::class)->bookingSummary(['property_id' => $property->id]);

    expect($summary)->not->toBeEmpty();
});

it('creates documents through DocumentService', function (): void {
    $document = app(DocumentService::class)->create([
        'name' => 'Lease packet',
        'ownerspecific' => 'no',
    ]);

    expect($document->name)->toBe('Lease packet');
});

it('imports owners through ImportService', function (): void {
    $count = app(ImportService::class)->importOwners([
        ['full_name' => 'Ada Owner', 'owner_email' => 'ada@import.test'],
    ]);

    expect($count)->toBe(1)
        ->and(Owner::query()->where('owner_email', 'ada@import.test')->exists())->toBeTrue();
});

it('manages properties through PropertyService and http', function (): void {
    $admin = User::factory()->admin()->create();
    $property = app(PropertyService::class)->create([
        'property_title' => 'Lake House',
        'status' => true,
    ]);

    expect($property->property_title)->toBe('Lake House');

    $this->actingAs($admin)
        ->getJson(route('admin.properties.index'))
        ->assertOk()
        ->assertJsonFragment(['property_title' => 'Lake House']);
});
