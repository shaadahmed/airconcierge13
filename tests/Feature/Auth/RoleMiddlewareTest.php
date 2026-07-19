<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);

    Route::middleware(['web', 'auth', 'role:superadmin'])
        ->get('/__test/superadmin-only', fn () => response('ok'))
        ->name('test.superadmin-only');
});

it('allows a user with the required role', function (): void {
    $user = User::factory()->create();
    $user->assignRole('superadmin');

    $this->actingAs($user)
        ->get('/__test/superadmin-only')
        ->assertOk()
        ->assertSee('ok');
});

it('forbids a user without the required role', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Property Owner');

    $this->actingAs($user)
        ->get('/__test/superadmin-only')
        ->assertForbidden();
});
