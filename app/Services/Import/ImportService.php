<?php

namespace App\Services\Import;

use App\Models\Guest;
use App\Models\ImportedEmail;
use App\Models\Owner;
use App\Models\Property;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ImportService
{
    /**
     * @param  list<array{full_name?: string, first_name?: string, last_name?: string, owner_email?: string, owner_phone?: string, region_id?: int}>  $rows
     */
    public function importOwners(array $rows): int
    {
        $count = 0;

        DB::transaction(function () use ($rows, &$count): void {
            foreach ($rows as $row) {
                Owner::query()->updateOrCreate(
                    ['owner_email' => $row['owner_email'] ?? null],
                    [
                        'full_name' => $row['full_name'] ?? null,
                        'first_name' => $row['first_name'] ?? null,
                        'last_name' => $row['last_name'] ?? null,
                        'owner_phone' => $row['owner_phone'] ?? null,
                        'region_id' => $row['region_id'] ?? null,
                        'emailstatus' => 1,
                        'deleted' => false,
                    ],
                );
                $count++;
            }
        });

        return $count;
    }

    /**
     * @param  list<array{property_title: string, region_id?: int, street_address?: string, city?: string, state?: string, zipcode?: string, hostaway_listing_id?: int}>  $rows
     */
    public function importProperties(array $rows): int
    {
        $count = 0;

        DB::transaction(function () use ($rows, &$count): void {
            foreach ($rows as $row) {
                Property::query()->updateOrCreate(
                    [
                        'property_title' => $row['property_title'],
                        'hostaway_listing_id' => $row['hostaway_listing_id'] ?? null,
                    ],
                    [
                        'region_id' => $row['region_id'] ?? null,
                        'street_address' => $row['street_address'] ?? null,
                        'city' => $row['city'] ?? null,
                        'state' => $row['state'] ?? null,
                        'zipcode' => $row['zipcode'] ?? null,
                        'status' => true,
                        'deleted' => false,
                        'created_date' => now(),
                    ],
                );
                $count++;
            }
        });

        return $count;
    }

    /**
     * @param  list<array{guest_name?: string, email?: string, phone?: string}>  $rows
     */
    public function importGuests(array $rows): int
    {
        $count = 0;

        foreach ($rows as $row) {
            Guest::query()->updateOrCreate(
                ['email' => $row['email'] ?? null],
                [
                    'guest_name' => $row['guest_name'] ?? 'Unknown',
                    'phone' => $row['phone'] ?? null,
                    'deleted' => false,
                ],
            );
            $count++;
        }

        return $count;
    }

    /**
     * @param  array{source?: string, subject?: string, body?: string, from_email?: string}  $attributes
     */
    public function storeImportedEmail(array $attributes): ImportedEmail
    {
        return ImportedEmail::query()->create([
            ...$attributes,
            'status' => 'imported',
        ]);
    }

    /**
     * @return Collection<int, ImportedEmail>
     */
    public function listImportedEmails()
    {
        return ImportedEmail::query()->latest('id')->get();
    }
}
