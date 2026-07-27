<?php

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a country with optional lat lng defaulting to zero', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson(route('admin.countries.store'), [
            'name' => 'Testland',
            'code' => 'TL',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Testland')
        ->assertJsonPath('data.code', 'TL')
        ->assertJsonPath('data.lat', 0)
        ->assertJsonPath('data.lng', 0);

    expect(Country::query()->where('code', 'TL')->exists())->toBeTrue();
});

it('creates a country with explicit lat lng', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson(route('admin.countries.store'), [
            'name' => 'Coordland',
            'code' => 'CL',
            'lat' => 12.5,
            'lng' => -45.25,
        ])
        ->assertCreated()
        ->assertJsonPath('data.lat', 12.5)
        ->assertJsonPath('data.lng', -45.25);
});

it('creates a state under a country', function (): void {
    $admin = User::factory()->admin()->create();
    $country = Country::query()->create([
        'name' => 'Parent',
        'code' => 'PR',
        'lat' => 0,
        'lng' => 0,
    ]);

    $this->actingAs($admin)
        ->postJson(route('admin.countries.states.store', $country), [
            'name' => 'State One',
            'code' => 'S1',
            'lat' => 1,
            'lng' => 2,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'State One')
        ->assertJsonPath('data.code', 'S1');
});
