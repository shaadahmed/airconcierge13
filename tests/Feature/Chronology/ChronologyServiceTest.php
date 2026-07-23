<?php

use App\Models\Chronology;
use App\Models\ChronologyMail;
use App\Models\ChronologyOrder;
use App\Models\EmailTemplate;
use App\Models\OutboundEmailLog;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Subregion;
use App\Services\Chronology\ChronologyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('creates a chronology and processes a due send idempotently', function (): void {
    Mail::fake();

    $subregion = Subregion::factory()->create();
    $owner = Owner::factory()->create(['emailstatus' => 1]);
    $property = Property::factory()->live()->create([
        'subregion_id' => $subregion->id,
        'region_id' => $subregion->region_id,
        'created_date' => now('America/Los_Angeles')->subDays(2),
    ]);
    $owner->properties()->attach($property->id);

    $template = EmailTemplate::factory()->create([
        'templatesubject' => 'Welcome',
        'description' => '<p>Hello owner</p>',
    ]);

    $service = app(ChronologyService::class);

    $chronology = $service->create([
        'name' => 'Onboarding',
        'startdate' => now()->subDays(10)->toDateString(),
        'chronologyoption' => 0,
        'subregion_ids' => [$subregion->id],
    ]);

    ChronologyOrder::factory()->create([
        'chronology_id' => $chronology->id,
        'day' => 0,
        'time' => '00',
        'minute' => '00',
        'previousaction' => '0',
        'template_id' => $template->id,
    ]);

    $sent = $service->processDueSends(now('America/Los_Angeles'));
    $again = $service->processDueSends(now('America/Los_Angeles'));

    expect($sent)->toBe(1)
        ->and($again)->toBe(0)
        ->and(ChronologyMail::query()->count())->toBe(1)
        ->and(OutboundEmailLog::query()->count())->toBe(1)
        ->and(Chronology::query()->where('name', 'Onboarding')->exists())->toBeTrue();
});
