<?php

namespace App\Models;

use Database\Factories\SubregionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['region_id', 'name', 'transient_occupancy_tax', 'short_stay_len'])]
class Subregion extends Model
{
    /** @use HasFactory<SubregionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * @return HasMany<SubregionBookingLimit, $this>
     */
    public function bookingLimits(): HasMany
    {
        return $this->hasMany(SubregionBookingLimit::class);
    }

    /**
     * @return HasMany<SubregionPermit, $this>
     */
    public function permits(): HasMany
    {
        return $this->hasMany(SubregionPermit::class);
    }

    /**
     * Flatten related limit/permit rows for the admin SPA payload shape.
     *
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        $limit = $this->bookingLimits->first();
        $permit = $this->permits->first();
        $limited = $limit !== null && $limit->limit_type !== null;

        return [
            'id' => $this->id,
            'region_id' => $this->region_id,
            'name' => $this->name,
            'transient_occupancy_tax' => $this->transient_occupancy_tax,
            'short_stay_len' => $this->short_stay_len,
            'limited_bookings' => $limited ? 'Yes' : 'No',
            'limit_type' => $limit?->limit_type?->value,
            'limit_value' => $limit?->limit_value,
            'bookings_limit' => $limit?->limit_type?->value === 'RESERVATIONS' ? $limit->limit_value : null,
            'nights_limit' => $limit?->limit_type?->value === 'NIGHTS' ? $limit->limit_value : null,
            'limit_filter' => $limit?->limit_filter,
            'business_license_account' => $permit?->business_license_account ?? 0,
            'permit_no' => $permit?->permit_no,
            'permit_issue_date' => $permit?->permit_issue_date?->format('Y-m-d'),
            'permit_expiry_date' => $permit?->permit_expiry_date?->format('Y-m-d'),
            'permit_length' => $permit?->permit_length,
            'region' => $this->region ? [
                'id' => $this->region->id,
                'name' => $this->region->name,
                'code' => $this->region->code,
                'color' => $this->region->color,
            ] : null,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transient_occupancy_tax' => 'decimal:2',
            'short_stay_len' => 'integer',
        ];
    }
}
