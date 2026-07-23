<?php

use App\Enums\UserRole;
use App\Models\Owner;
use App\Models\OwnerTermsAgreement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reports terms agreed when an owner terms agreement is recorded', function (): void {
    $user = User::factory()->owner()->create();
    OwnerTermsAgreement::factory()->create([
        'user_id' => $user->id,
        'agreed_terms' => true,
    ]);

    expect($user->fresh()->hasAgreedToTerms())->toBeTrue();
});

it('reports terms outstanding when an owner has no agreement', function (): void {
    $user = User::factory()->owner()->create();

    expect($user->hasAgreedToTerms())->toBeFalse();
});

it('grants active access when the owner has a live property', function (): void {
    $user = User::factory()->owner()->create();
    $owner = Owner::factory()->create();
    $property = Property::factory()->live()->create();

    $owner->users()->attach($user->id);
    $owner->properties()->attach($property->id);

    expect($user->fresh()->hasActiveAccess())->toBeTrue();
});

it('denies active access when the owner only has inactive properties', function (): void {
    $user = User::factory()->owner()->create();
    $owner = Owner::factory()->create();
    $property = Property::factory()->inactive()->create();

    $owner->users()->attach($user->id);
    $owner->properties()->attach($property->id);

    expect($user->fresh()->hasActiveAccess())->toBeFalse();
});

it('treats non-owner roles as always agreeing and having active access', function (): void {
    $admin = User::factory()->admin()->create();

    expect($admin->hasAgreedToTerms())->toBeTrue()
        ->and($admin->hasActiveAccess())->toBeTrue()
        ->and($admin->role)->toBe(UserRole::Admin);
});

it('redirects owners without terms agreement away from owner statements', function (): void {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->get(route('admin.owner-statements.index'))
        ->assertRedirect(route('admin.terms.show'));
});

it('lets owners with terms and a live property reach owner statements', function (): void {
    $user = User::factory()->owner()->create();
    OwnerTermsAgreement::factory()->create([
        'user_id' => $user->id,
        'agreed_terms' => true,
    ]);

    $owner = Owner::factory()->create();
    $property = Property::factory()->live()->create();
    $owner->users()->attach($user->id);
    $owner->properties()->attach($property->id);

    $this->actingAs($user->fresh())
        ->get(route('admin.owner-statements.index'))
        ->assertOk();
});
