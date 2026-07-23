<?php

use App\Models\HostawayAccessToken;
use App\Services\Hostaway\HostawayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config([
        'services.hostaway.base_url' => 'https://api.hostaway.test/v1/',
        'services.hostaway.account_id' => 'account-1',
        'services.hostaway.api_key' => 'secret-key',
    ]);
});

it('fetches a reservation using a stored non-expired token', function (): void {
    Http::preventStrayRequests();

    HostawayAccessToken::factory()->create([
        'access_token' => 'stored-token',
        'token_type' => 'Bearer',
        'expiry' => now()->addMonth()->toDateString(),
    ]);

    Http::fake([
        'api.hostaway.test/v1/reservations/42' => Http::response([
            'result' => ['id' => 42, 'status' => 'new'],
        ], 200),
    ]);

    $result = app(HostawayService::class)->getReservations(42);

    expect($result)->toMatchArray([
        'result' => ['id' => 42, 'status' => 'new'],
    ]);

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://api.hostaway.test/v1/reservations/42'
            && $request->hasHeader('Authorization', 'Bearer stored-token');
    });
});

it('refreshes and stores an access token when none are valid', function (): void {
    Http::preventStrayRequests();

    HostawayAccessToken::factory()->expired()->create();

    Http::fake([
        'api.hostaway.test/v1/accessTokens' => Http::response([
            'access_token' => 'fresh-token',
            'token_type' => 'Bearer',
            'expires_in' => 86400,
        ], 200),
        'api.hostaway.test/v1/listings/9' => Http::response([
            'result' => ['id' => 9, 'name' => 'Beach House'],
        ], 200),
    ]);

    $result = app(HostawayService::class)->getListing(9);

    expect($result['result']['name'] ?? null)->toBe('Beach House')
        ->and(HostawayAccessToken::query()->where('access_token', 'fresh-token')->exists())->toBeTrue();
});

it('fails closed when api credentials are missing', function (): void {
    config([
        'services.hostaway.account_id' => '',
        'services.hostaway.api_key' => '',
    ]);

    Http::preventStrayRequests();
    Http::fake();

    $result = app(HostawayService::class)->getReservations(1);

    expect($result)->toHaveKey('error')
        ->and(HostawayAccessToken::query()->count())->toBe(0);

    Http::assertNothingSent();
});
