<?php

use App\Models\Booking;
use App\Models\Dashboard;
use App\Models\Property;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns report summary for staff via Form Request filters', function (): void {
    $admin = User::factory()->admin()->create();
    $property = Property::factory()->live()->create();
    Booking::factory()->create([
        'property_id' => $property->id,
        'total_guest_paid' => 150,
        'cancelled_booking' => false,
    ]);

    $this->actingAs($admin)
        ->getJson(route('admin.reports.index', ['property_id' => $property->id]))
        ->assertOk()
        ->assertJsonStructure(['data' => ['summary', 'regions']]);
});

it('rejects invalid report year via Form Request', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson(route('admin.reports.index', ['year' => 1990]))
        ->assertUnprocessable();
});

it('forbids owners from viewing reports', function (): void {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)
        ->getJson(route('admin.reports.index'))
        ->assertForbidden();
});

it('returns dashboard revenue chart for staff', function (): void {
    $admin = User::factory()->admin()->create();
    Booking::factory()->create([
        'reservation_start_date' => now()->toDateString(),
        'total_guest_paid' => 200,
        'cancelled_booking' => false,
    ]);

    $this->actingAs($admin)
        ->getJson(route('admin.dashboard.revenue-chart', ['months' => 3]))
        ->assertOk()
        ->assertJsonStructure(['data' => ['labels', 'values']]);
});

it('rejects invalid dashboard months via Form Request', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson(route('admin.dashboard.revenue-chart', ['months' => 99]))
        ->assertUnprocessable();
});

it('authorizes staff for report and dashboard policies', function (): void {
    $admin = User::factory()->admin()->create();
    $owner = User::factory()->owner()->create();

    expect($admin->can('viewAny', Report::class))->toBeTrue()
        ->and($admin->can('viewAny', Dashboard::class))->toBeTrue()
        ->and($owner->can('viewAny', Report::class))->toBeFalse()
        ->and($owner->can('viewAny', Dashboard::class))->toBeFalse();
});
