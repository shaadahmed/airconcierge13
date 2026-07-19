<?php

use App\Contracts\OwnerActiveAccessChecker;
use App\Contracts\OwnerTermsChecker;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

it('lets property owners through when stub checkers allow access', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Property Owner');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('redirects property owners who have not agreed to terms', function (): void {
    $this->app->bind(OwnerTermsChecker::class, fn () => new class implements OwnerTermsChecker
    {
        public function hasAgreed(User $user): bool
        {
            return false;
        }
    });

    $user = User::factory()->create();
    $user->assignRole('Property Owner');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.terms.show'));
});

it('redirects property owners without active access to owner statements', function (): void {
    $this->app->bind(OwnerActiveAccessChecker::class, fn () => new class implements OwnerActiveAccessChecker
    {
        public function hasActiveAccess(User $user): bool
        {
            return false;
        }
    });

    $user = User::factory()->create();
    $user->assignRole('Property Owner');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.owner-statements.index'));
});

it('still allows restricted owners to reach owner statements', function (): void {
    $this->app->bind(OwnerActiveAccessChecker::class, fn () => new class implements OwnerActiveAccessChecker
    {
        public function hasActiveAccess(User $user): bool
        {
            return false;
        }
    });

    $user = User::factory()->create();
    $user->assignRole('Property Owner');

    $this->actingAs($user)
        ->get(route('admin.owner-statements.index'))
        ->assertOk();
});
