<?php

use App\Jobs\SendOutboundEmailJob;
use App\Models\DynamicContent;
use App\Models\OwnerTermsAgreement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    DynamicContent::factory()->create([
        'page_id' => DynamicContent::OWNER_AGREEMENT_PAGE_ID,
        'content' => '<p>Test owner agreement.</p>',
    ]);
});

it('returns owner terms content and agreed flag', function (): void {
    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->getJson(route('admin.terms.show'))
        ->assertOk()
        ->assertJsonPath('data.content', '<p>Test owner agreement.</p>')
        ->assertJsonPath('data.agreed', false);
});

it('records agreement and queues staff notification', function (): void {
    Queue::fake();

    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->postJson(route('admin.terms.agree'))
        ->assertOk()
        ->assertJsonPath('data.agreed', true)
        ->assertJsonPath('data.redirect_to', '/admin/dashboard');

    expect(OwnerTermsAgreement::query()->where('user_id', $user->id)->where('agreed_terms', true)->exists())->toBeTrue();

    Queue::assertPushed(SendOutboundEmailJob::class);
});

it('records disagreement, logs the owner out, and returns login redirect', function (): void {
    Queue::fake();

    $user = User::factory()->owner()->create();

    $this->actingAs($user)
        ->postJson(route('admin.terms.disagree'))
        ->assertOk()
        ->assertJsonPath('data.agreed', false)
        ->assertJsonPath('data.logout', true)
        ->assertJsonPath('data.redirect_to', '/login');

    expect(OwnerTermsAgreement::query()->where('user_id', $user->id)->where('agreed_terms', false)->exists())->toBeTrue()
        ->and(auth()->check())->toBeFalse();

    Queue::assertPushed(SendOutboundEmailJob::class);
});
