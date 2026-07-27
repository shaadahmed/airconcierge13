<?php

use App\Models\User;
use Database\Seeders\ResourceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(ResourceSeeder::class);
});

it('returns grouped navigation for staff and excludes soft-deleted resources', function (): void {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->getJson(route('admin.navigation'))
        ->assertOk()
        ->json('data');

    $titles = collect($response)->pluck('title')->all();

    expect($titles)->toContain('Dashboard', 'Properties', 'Financials', 'Staff', 'Reporting', 'Administration')
        ->and($titles)->not->toContain('Payments');

    $reporting = collect($response)->firstWhere('title', 'Reporting');
    $childTitles = collect($reporting['children'] ?? [])->pluck('title')->all();

    expect($childTitles)->toContain('Guest Location')
        ->and($childTitles)->toContain('Reservation | City Limit')
        ->and(collect($reporting['children'] ?? [])->where('title', 'Guest Location')->count())->toBe(1);
});

it('hides superadmin-only administration items from admins', function (): void {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->getJson(route('admin.navigation'))
        ->assertOk()
        ->json('data');

    $administration = collect($response)->firstWhere('title', 'Administration');
    $childTitles = collect($administration['children'] ?? [])->pluck('title')->all();

    expect($childTitles)->toContain('Manage Content', 'Cron & database rules', 'My Profile')
        ->and($childTitles)->not->toContain('Manage Users', 'Air BNB Emails', 'Critical Actions');
});

it('includes superadmin administration items for superadmins', function (): void {
    $superAdmin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($superAdmin)
        ->getJson(route('admin.navigation'))
        ->assertOk()
        ->json('data');

    $administration = collect($response)->firstWhere('title', 'Administration');
    $childTitles = collect($administration['children'] ?? [])->pluck('title')->all();

    expect($childTitles)->toContain('Air BNB Emails', 'Manage Users', 'System Resources', 'Critical Actions', 'My Profile');
});

it('returns empty navigation for owners', function (): void {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)
        ->getJson(route('admin.navigation'))
        ->assertOk()
        ->assertJson(['data' => []]);
});
