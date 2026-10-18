<?php

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists countries with nested states and cities for staff', function (): void {
    $admin = User::factory()->admin()->create();
    $country = Country::factory()->create(['name' => 'United States', 'code' => 'US']);
    $state = State::factory()->for($country)->create(['name' => 'California', 'code' => 'CA']);
    City::factory()->for($state)->create(['name' => 'Napa', 'code' => 'NAPA']);

    $this->actingAs($admin)
        ->getJson(route('admin.countries.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'United States')
        ->assertJsonPath('data.0.states.0.name', 'California')
        ->assertJsonPath('data.0.states.0.cities.0.name', 'Napa');
});

it('creates updates and deletes a country', function (): void {
    $admin = User::factory()->admin()->create();

    $create = $this->actingAs($admin)
        ->postJson(route('admin.countries.store'), [
            'name' => 'Canada',
            'code' => 'CA',
            'lat' => 56.13,
            'lng' => -106.35,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Canada');

    $countryId = $create->json('data.id');

    $this->actingAs($admin)
        ->putJson(route('admin.countries.update', $countryId), [
            'name' => 'Canada Updated',
            'code' => 'CA',
            'lat' => 56.13,
            'lng' => -106.35,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Canada Updated');

    $this->actingAs($admin)
        ->deleteJson(route('admin.countries.destroy', $countryId))
        ->assertSuccessful();

    expect(Country::query()->find($countryId))->toBeNull();
    expect(Country::withTrashed()->find($countryId))->not->toBeNull();
});

it('manages nested states and cities', function (): void {
    $admin = User::factory()->admin()->create();
    $country = Country::factory()->create();

    $stateResponse = $this->actingAs($admin)
        ->postJson(route('admin.countries.states.store', $country), [
            'name' => 'Nevada',
            'code' => 'NV',
            'lat' => 38.8,
            'lng' => -116.42,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Nevada');

    $stateId = $stateResponse->json('data.id');

    $cityResponse = $this->actingAs($admin)
        ->postJson(route('admin.countries.states.cities.store', [$country, $stateId]), [
            'name' => 'Incline Village',
            'code' => 'IV',
            'zip' => 89451,
            'contact_code' => null,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Incline Village');

    $cityId = $cityResponse->json('data.id');

    $this->actingAs($admin)
        ->putJson(route('admin.countries.states.cities.update', [$country, $stateId, $cityId]), [
            'name' => 'Incline Village',
            'code' => 'IV',
            'zip' => 89452,
            'contact_code' => 'IV',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.zip', 89452);

    $this->actingAs($admin)
        ->deleteJson(route('admin.countries.states.cities.destroy', [$country, $stateId, $cityId]))
        ->assertSuccessful();

    $this->actingAs($admin)
        ->deleteJson(route('admin.countries.states.destroy', [$country, $stateId]))
        ->assertSuccessful();

    expect(State::query()->find($stateId))->toBeNull();
    expect(City::query()->find($cityId))->toBeNull();
});

it('requires city names to be unique within a state but not across states', function (): void {
    $admin = User::factory()->admin()->create();
    $country = Country::factory()->create();
    $california = State::factory()->for($country)->create(['name' => 'California', 'code' => 'CA']);
    $nevada = State::factory()->for($country)->create(['name' => 'Nevada', 'code' => 'NV']);

    $this->actingAs($admin)
        ->postJson(route('admin.countries.states.cities.store', [$country, $california]), [
            'name' => 'Springfield',
            'code' => 'SP',
        ])
        ->assertCreated();

    $this->actingAs($admin)
        ->postJson(route('admin.countries.states.cities.store', [$country, $california]), [
            'name' => 'Springfield',
            'code' => 'SP2',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    $this->actingAs($admin)
        ->postJson(route('admin.countries.states.cities.store', [$country, $nevada]), [
            'name' => 'Springfield',
            'code' => 'SP',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Springfield');
});

it('forbids owners from managing countries', function (): void {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)
        ->getJson(route('admin.countries.index'))
        ->assertForbidden();
});
