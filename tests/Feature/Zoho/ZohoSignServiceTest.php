<?php

use App\Models\ZohoCodeDetail;
use App\Services\Zoho\ZohoSignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('reuses a fresh zoho access token without refreshing', function (): void {
    config([
        'services.zoho.client_id' => 'id',
        'services.zoho.client_secret' => 'secret',
        'services.zoho.account_url' => 'https://accounts.example.test',
        'services.zoho.api_base_url' => 'https://sign.example.test/api/v1',
    ]);

    ZohoCodeDetail::factory()->create([
        'zoho_access_token' => 'fresh-token',
        'update_dtm' => now(),
    ]);

    Http::fake();

    $token = app(ZohoSignService::class)->ensureAccessToken();

    expect($token)->toBe('fresh-token');
    Http::assertNothingSent();
});

it('refreshes an expired zoho access token', function (): void {
    config([
        'services.zoho.client_id' => 'id',
        'services.zoho.client_secret' => 'secret',
        'services.zoho.account_url' => 'https://accounts.example.test',
        'services.zoho.api_base_url' => 'https://sign.example.test/api/v1',
    ]);

    ZohoCodeDetail::factory()->expired()->create([
        'zoho_access_token' => 'old-token',
        'zoho_refresh_token' => 'refresh-token',
    ]);

    Http::fake([
        'accounts.example.test/oauth/v2/token' => Http::response(['access_token' => 'new-token']),
    ]);

    $token = app(ZohoSignService::class)->ensureAccessToken();

    expect($token)->toBe('new-token')
        ->and(ZohoCodeDetail::query()->first()?->zoho_access_token)->toBe('new-token');
});

it('creates a zoho document from a template', function (): void {
    config([
        'services.zoho.client_id' => 'id',
        'services.zoho.client_secret' => 'secret',
        'services.zoho.account_url' => 'https://accounts.example.test',
        'services.zoho.api_base_url' => 'https://sign.example.test/api/v1',
    ]);

    ZohoCodeDetail::factory()->create([
        'zoho_access_token' => 'fresh-token',
        'update_dtm' => now(),
    ]);

    Http::fake([
        'sign.example.test/api/v1/templates/*' => Http::response([
            'requests' => ['request_id' => 'req-1', 'document_ids' => ['doc-1']],
        ]),
    ]);

    $response = app(ZohoSignService::class)->createDocumentFromTemplate('tmpl-1', ['templates' => []]);

    expect($response['requests']['request_id'])->toBe('req-1');
});
