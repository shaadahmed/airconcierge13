<?php

use App\Models\Chronology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows staff to list and create chronologies', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson(route('admin.chronologies.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->postJson(route('admin.chronologies.store'), [
            'name' => 'Spring drip',
            'startdate' => now()->toDateString(),
            'chronologyoption' => 0,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Spring drip');

    expect(Chronology::query()->where('name', 'Spring drip')->exists())->toBeTrue();
});

it('forbids owners from managing chronologies', function (): void {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)
        ->getJson(route('admin.chronologies.index'))
        ->assertForbidden();
});
