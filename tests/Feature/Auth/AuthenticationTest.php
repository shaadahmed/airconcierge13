<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects browser guests to the SPA login URL', function (): void {
    config(['app.frontend_url' => 'http://localhost:3000']);

    $this->get(route('login'))
        ->assertRedirect('http://localhost:3000/login');
});

it('redirects guests away from the admin dashboard', function (): void {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

it('authenticates users with valid credentials via HTML form', function (): void {
    $user = User::factory()->admin()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $this->post(route('login'), [
        'email' => 'admin@example.com',
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('authenticates users with valid credentials via JSON', function (): void {
    $user = User::factory()->admin()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $this->postJson(route('login'), [
        'email' => 'admin@example.com',
        'password' => 'password',
        'remember' => false,
    ])
        ->assertOk()
        ->assertJsonPath('data.email', 'admin@example.com');

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    $this->post(route('login'), [
        'email' => 'admin@example.com',
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs out an authenticated user to the SPA login URL', function (): void {
    config(['app.frontend_url' => 'http://localhost:3000']);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect('http://localhost:3000/login');

    $this->assertGuest();
});
