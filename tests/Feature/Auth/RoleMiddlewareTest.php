<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::middleware(['web', 'auth', 'can:accessSuperAdminArea,'.User::class])
        ->get('/__test/superadmin-only', fn () => response('ok'))
        ->name('test.superadmin-only');
});

it('allows a user with the required role', function (): void {
    $user = User::factory()->superAdmin()->create();

    $this->actingAs($user)
        ->get('/__test/superadmin-only')
        ->assertOk()
        ->assertSee('ok');
});

it('forbids a user without the required role', function (): void {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->get('/__test/superadmin-only')
        ->assertForbidden();
});
