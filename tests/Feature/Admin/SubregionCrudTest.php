<?php

use App\Models\Region;
use App\Models\Subregion;
use App\Models\SubregionBookingLimit;
use App\Models\SubregionPermit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a subregion with booking limit and permit', function (): void {
    $admin = User::factory()->admin()->create();
    $region = Region::factory()->create();

    $response = $this->actingAs($admin)
        ->postJson(route('admin.regions.subregions.store'), [
            'region_id' => $region->id,
            'name' => 'South Lake Tahoe',
            'transient_occupancy_tax' => 12.5,
            'short_stay_len' => 30,
            'limited_bookings' => 'Yes',
            'limit_type' => 'NIGHTS',
            'nights_limit' => 180,
            'limit_filter' => 'CALENDAR',
            'business_license_account' => 1,
            'permit_no' => 'SLT-100',
            'permit_issue_date' => '2025-01-15',
            'permit_length' => 12,
            'permit_expiry_date' => '2026-01-15',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'South Lake Tahoe')
        ->assertJsonPath('data.limited_bookings', 'Yes')
        ->assertJsonPath('data.limit_type', 'NIGHTS')
        ->assertJsonPath('data.nights_limit', 180)
        ->assertJsonPath('data.permit_no', 'SLT-100')
        ->assertJsonPath('data.region.id', $region->id);

    $subregionId = $response->json('data.id');

    expect(SubregionBookingLimit::query()->where('subregion_id', $subregionId)->count())->toBe(1)
        ->and(SubregionPermit::query()->where('subregion_id', $subregionId)->count())->toBe(1);
});

it('lists subregions filtered by region and clears limits when disabled', function (): void {
    $admin = User::factory()->admin()->create();
    $region = Region::factory()->create();
    $other = Region::factory()->create();
    $subregion = Subregion::factory()->for($region)->create(['name' => 'Incline']);
    Subregion::factory()->for($other)->create(['name' => 'Other']);
    SubregionBookingLimit::factory()->for($subregion)->create([
        'limit_type' => 'RESERVATIONS',
        'limit_value' => 90,
        'limit_filter' => 'PERMIT_YEAR',
    ]);

    $this->actingAs($admin)
        ->getJson(route('admin.regions.subregions.index', ['region_id' => $region->id]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Incline')
        ->assertJsonPath('data.0.limited_bookings', 'Yes');

    $this->actingAs($admin)
        ->putJson(route('admin.regions.subregions.update', $subregion), [
            'region_id' => $region->id,
            'name' => 'Incline Village',
            'transient_occupancy_tax' => 13,
            'short_stay_len' => 28,
            'limited_bookings' => 'No',
            'business_license_account' => 0,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.limited_bookings', 'No')
        ->assertJsonPath('data.limit_type', null);

    expect(SubregionBookingLimit::query()->where('subregion_id', $subregion->id)->count())->toBe(0);
});

it('soft deletes a subregion and related rows', function (): void {
    $admin = User::factory()->admin()->create();
    $subregion = Subregion::factory()->create();
    SubregionBookingLimit::factory()->for($subregion)->create();
    SubregionPermit::factory()->for($subregion)->create();

    $this->actingAs($admin)
        ->deleteJson(route('admin.regions.subregions.destroy', $subregion))
        ->assertSuccessful();

    expect(Subregion::query()->find($subregion->id))->toBeNull()
        ->and(SubregionBookingLimit::query()->count())->toBe(0)
        ->and(SubregionPermit::query()->count())->toBe(0);
});
