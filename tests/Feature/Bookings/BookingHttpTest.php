<?php

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows staff to list and create bookings', function (): void {
    $admin = User::factory()->admin()->create();
    $property = Property::factory()->create();

    $this->actingAs($admin)
        ->getJson(route('admin.bookings.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->postJson(route('admin.bookings.store'), [
            'property_id' => $property->id,
            'booking_code' => 'HTTP-1',
            'reservation_start_date' => '2026-11-01',
            'reservation_end_date' => '2026-11-03',
        ])
        ->assertCreated()
        ->assertJsonPath('data.booking_code', 'HTTP-1');

    expect(Booking::query()->where('booking_code', 'HTTP-1')->exists())->toBeTrue();
});

it('forbids owners from managing bookings', function (): void {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)
        ->getJson(route('admin.bookings.index'))
        ->assertForbidden();
});
