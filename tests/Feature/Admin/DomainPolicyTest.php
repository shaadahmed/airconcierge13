<?php

use App\Models\DocumentUpload;
use App\Models\ImportedEmail;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows staff and denies owners for document property and import policies', function (): void {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $owner = User::factory()->owner()->create();

    $document = DocumentUpload::query()->create(['name' => 'Lease']);
    $property = Property::factory()->live()->create();
    $email = ImportedEmail::query()->create([
        'source' => 'test',
        'subject' => 'Hi',
    ]);

    foreach ([$admin, $manager] as $staff) {
        expect($staff->can('viewAny', DocumentUpload::class))->toBeTrue()
            ->and($staff->can('create', DocumentUpload::class))->toBeTrue()
            ->and($staff->can('update', $document))->toBeTrue()
            ->and($staff->can('delete', $document))->toBeTrue()
            ->and($staff->can('viewAny', Property::class))->toBeTrue()
            ->and($staff->can('create', Property::class))->toBeTrue()
            ->and($staff->can('update', $property))->toBeTrue()
            ->and($staff->can('delete', $property))->toBeTrue()
            ->and($staff->can('viewAny', ImportedEmail::class))->toBeTrue()
            ->and($staff->can('create', ImportedEmail::class))->toBeTrue()
            ->and($staff->can('view', $email))->toBeTrue();
    }

    expect($owner->can('viewAny', DocumentUpload::class))->toBeFalse()
        ->and($owner->can('viewAny', Property::class))->toBeFalse()
        ->and($owner->can('viewAny', ImportedEmail::class))->toBeFalse()
        ->and($owner->can('create', ImportedEmail::class))->toBeFalse();
});

it('forbids owners from listing properties and documents over http', function (): void {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)
        ->getJson(route('admin.properties.index'))
        ->assertForbidden();

    $this->actingAs($owner)
        ->getJson(route('admin.documents.index'))
        ->assertForbidden();

    $this->actingAs($owner)
        ->getJson(route('admin.imports.emails.index'))
        ->assertForbidden();
});

it('allows staff to list properties documents and imported emails', function (): void {
    $admin = User::factory()->admin()->create();
    Property::factory()->live()->create(['property_title' => 'Policy House']);
    DocumentUpload::query()->create(['name' => 'Policy Doc']);

    $this->actingAs($admin)
        ->getJson(route('admin.properties.index'))
        ->assertOk()
        ->assertJsonFragment(['property_title' => 'Policy House']);

    $this->actingAs($admin)
        ->getJson(route('admin.documents.index'))
        ->assertOk()
        ->assertJsonFragment(['name' => 'Policy Doc']);

    $this->actingAs($admin)
        ->getJson(route('admin.imports.emails.index'))
        ->assertOk();
});
