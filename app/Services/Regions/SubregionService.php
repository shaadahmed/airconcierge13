<?php

namespace App\Services\Regions;

use App\Enums\BookingLimitType;
use App\Models\Subregion;
use App\Models\SubregionBookingLimit;
use App\Models\SubregionPermit;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates subregion create/update with related booking-limit and permit rows.
 */
class SubregionService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Subregion
    {
        return DB::transaction(function () use ($data): Subregion {
            $subregion = Subregion::query()->create($this->coreAttributes($data));
            $this->syncBookingLimit($subregion, $data);
            $this->syncPermit($subregion, $data);

            return $this->freshForAdmin($subregion);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Subregion $subregion, array $data): Subregion
    {
        return DB::transaction(function () use ($subregion, $data): Subregion {
            $subregion->update($this->coreAttributes($data));
            $this->syncBookingLimit($subregion, $data);
            $this->syncPermit($subregion, $data);

            return $this->freshForAdmin($subregion);
        });
    }

    public function delete(Subregion $subregion): void
    {
        DB::transaction(function () use ($subregion): void {
            $subregion->bookingLimits()->each(fn (SubregionBookingLimit $limit) => $limit->delete());
            $subregion->permits()->each(fn (SubregionPermit $permit) => $permit->delete());
            $subregion->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function coreAttributes(array $data): array
    {
        return [
            'region_id' => $data['region_id'] ?? null,
            'name' => $data['name'] ?? null,
            'transient_occupancy_tax' => $data['transient_occupancy_tax'] ?? null,
            'short_stay_len' => $data['short_stay_len'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncBookingLimit(Subregion $subregion, array $data): void
    {
        $limited = ($data['limited_bookings'] ?? null) === 'Yes'
            || filled($data['limit_type'] ?? null);

        $existing = $subregion->bookingLimits()->first();

        if (! $limited) {
            $existing?->delete();

            return;
        }

        $limitType = BookingLimitType::from((string) $data['limit_type']);
        $limitValue = $data['limit_value']
            ?? ($limitType === BookingLimitType::Nights ? ($data['nights_limit'] ?? null) : ($data['bookings_limit'] ?? null));

        $attributes = [
            'limit_type' => $limitType,
            'limit_value' => $limitValue,
            'limit_filter' => $data['limit_filter'] ?? null,
        ];

        if ($existing) {
            $existing->update($attributes);

            return;
        }

        $subregion->bookingLimits()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncPermit(Subregion $subregion, array $data): void
    {
        $hasPermitPayload = array_key_exists('business_license_account', $data)
            || array_key_exists('permit_no', $data)
            || array_key_exists('permit_issue_date', $data)
            || array_key_exists('permit_expiry_date', $data)
            || array_key_exists('permit_length', $data);

        if (! $hasPermitPayload) {
            return;
        }

        $attributes = [
            'business_license_account' => (int) ($data['business_license_account'] ?? 0),
            'permit_no' => $data['permit_no'] ?? null,
            'permit_issue_date' => $data['permit_issue_date'] ?? null,
            'permit_expiry_date' => $data['permit_expiry_date'] ?? null,
            'permit_length' => $data['permit_length'] ?? null,
        ];

        $existing = $subregion->permits()->first();

        if ($existing) {
            $existing->update($attributes);

            return;
        }

        $subregion->permits()->create($attributes);
    }

    private function freshForAdmin(Subregion $subregion): Subregion
    {
        return $subregion->fresh(['region', 'bookingLimits', 'permits']) ?? $subregion;
    }
}
