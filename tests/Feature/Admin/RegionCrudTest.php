<?php

use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists creates updates and deletes regions for staff', function (): void {
    $manager = User::factory()->manager()->create();

    $create = $this->actingAs($manager)
        ->postJson(route('admin.regions.store'), [
            'name' => 'Lake Tahoe',
            'code' => 'LT',
            'color' => '#4caf50',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Lake Tahoe')
        ->assertJsonPath('data.code', 'LT');

    $regionId = $create->json('data.id');

    expect(Region::query()->find($regionId)?->name)->toBe('Lake Tahoe')
        ->and(Region::query()->find($regionId)?->code)->toBe('LT');

    $this->actingAs($manager)
        ->getJson(route('admin.regions.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Lake Tahoe');

    $this->actingAs($manager)
        ->putJson(route('admin.regions.update', $regionId), [
            'name' => 'Lake Tahoe Basin',
            'code' => 'LTB',
            'color' => '#2196f3',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Lake Tahoe Basin')
        ->assertJsonPath('data.code', 'LTB');

    $this->actingAs($manager)
        ->deleteJson(route('admin.regions.destroy', $regionId))
        ->assertSuccessful();

    expect(Region::query()->find($regionId))->toBeNull();
});

it('forbids cleaners from managing regions', function (): void {
    $cleaner = User::factory()->cleaner()->create();

    $this->actingAs($cleaner)
        ->postJson(route('admin.regions.store'), [
            'name' => 'Napa',
            'code' => 'NV',
            'color' => null,
        ])
        ->assertForbidden();
});
