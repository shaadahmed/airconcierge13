<?php

use App\Models\Booking;
use App\Models\Owner;
use App\Models\OwnerTermsAgreement;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function ownerWithProperty(): array
{
    $user = User::factory()->owner()->create();
    OwnerTermsAgreement::factory()->create([
        'user_id' => $user->id,
        'agreed_terms' => true,
    ]);

    $owner = Owner::factory()->create();
    $property = Property::factory()->live()->create();
    $owner->users()->attach($user->id);
    $owner->properties()->attach($property->id);

    Booking::factory()->create([
        'property_id' => $property->id,
        'month' => now()->format('m'),
        'year' => now()->format('Y'),
        'accomodations' => 100,
        'cleaning_fee' => 25,
        'tot_charged_to_guest' => 10,
        'management_fee' => 20,
        'owner_payout_amount_from_airconcierge' => 115,
        'no_of_nights' => 2,
        'cancelled_booking' => false,
        'deleted' => false,
    ]);

    return [$user->fresh(), $property];
}

it('lists scoped properties for owner statements', function (): void {
    [$user, $property] = ownerWithProperty();

    $this->actingAs($user)
        ->getJson(route('admin.owner-statements.index'))
        ->assertOk()
        ->assertJsonPath('data.properties.0.id', $property->id);
});

it('returns a profit and loss report for an accessible property', function (): void {
    [$user, $property] = ownerWithProperty();

    $this->actingAs($user)
        ->getJson(route('admin.owner-statements.report', [
            'property_id' => $property->id,
            'date_range' => 'this_month',
        ]))
        ->assertOk()
        ->assertJsonPath('data.accomodations', 100)
        ->assertJsonPath('data.cleaning_fee', 25)
        ->assertJsonPath('data.net_income', 115)
        ->assertJsonPath('data.total_reservations', 1);
});

it('rejects a property the owner cannot access', function (): void {
    [$user] = ownerWithProperty();
    $other = Property::factory()->live()->create();

    $this->actingAs($user)
        ->getJson(route('admin.owner-statements.report', [
            'property_id' => $other->id,
            'date_range' => 'this_month',
        ]))
        ->assertStatus(422);
});
