<?php

namespace App\Models;

use Database\Factories\SubregionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'region_id',
    'subregion_name',
    'airbnb_tot_region',
    'transient_occupancy_tax',
    'limit_type',
    'limit_value',
    'limit_filter',
    'short_stay_len',
    'business_license_account',
    'permit_no',
    'permit_issue_date',
    'permit_expiry_date',
    'permit_length',
    'deleted',
])]
class Subregion extends Model
{
    /** @use HasFactory<SubregionFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'airbnb_tot_region' => 'boolean',
            'transient_occupancy_tax' => 'decimal:2',
            'deleted' => 'boolean',
            'permit_issue_date' => 'date',
            'permit_expiry_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * @return HasMany<Property, $this>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->where('deleted', false)->orWhereNull('deleted');
        });
    }
}
