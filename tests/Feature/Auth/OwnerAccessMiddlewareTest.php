<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::middleware(['web', 'auth', 'owner.terms', 'owner.active'])
        ->get('/__test/owner-restricted', fn () => response('owner-ok'))
        ->name('test.owner-restricted');
});

it('lets property owners through owner routes when predicates allow access', function (): void {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->get(route('admin.owner-statements.index'))
        ->assertOk();
});

it('does not run owner middleware on the shared admin dashboard', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('lets staff reach the shared dashboard without owner middleware redirects', function (): void {
    $user = User::factory()->manager()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('redirects property owners who have not agreed to terms', function (): void {
    $user = User::factory()->owner()->create();

    $mocked = Mockery::mock($user)->makePartial();
    $mocked->shouldReceive('hasAgreedToTerms')->andReturn(false);
    $mocked->role = UserRole::Owner;

    $this->actingAs($mocked)
        ->get(route('admin.owner-statements.index'))
        ->assertRedirect(route('admin.terms.show'));
});

it('redirects property owners without active access to owner statements', function (): void {
    $user = User::factory()->owner()->create();

    $mocked = Mockery::mock($user)->makePartial();
    $mocked->shouldReceive('hasActiveAccess')->andReturn(false);
    $mocked->role = UserRole::Owner;

    $this->actingAs($mocked)
        ->get('/__test/owner-restricted')
        ->assertRedirect(route('admin.owner-statements.index'));
});

it('still allows restricted owners to reach owner statements', function (): void {
    $user = User::factory()->owner()->create();

    $mocked = Mockery::mock($user)->makePartial();
    $mocked->shouldReceive('hasActiveAccess')->andReturn(false);
    $mocked->role = UserRole::Owner;

    $this->actingAs($mocked)
        ->get(route('admin.owner-statements.index'))
        ->assertOk();
});

it('allows property owners to reach the terms page when terms are outstanding', function (): void {
    $user = User::factory()->owner()->create();

    $mocked = Mockery::mock($user)->makePartial();
    $mocked->shouldReceive('hasAgreedToTerms')->andReturn(false);
    $mocked->role = UserRole::Owner;

    $this->actingAs($mocked)
        ->get(route('admin.terms.show'))
        ->assertOk();
});
