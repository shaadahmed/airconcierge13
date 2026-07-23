<?php

namespace App\Models;

use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'region_id',
    'subregion_id',
    'hostaway_listing_id',
    'property_title',
    'limit_type',
    'limit_value',
    'email_title',
    'unit_number',
    'street_address',
    'city',
    'state',
    'zipcode',
    'created_date',
    'created_by',
    'modified_date',
    'last_modified_by',
    'status',
    'inactive_date',
    'contract_start_date',
    'contract_end_date',
    'contract_end_reason',
    'management_type_id',
    'ac_management_fee',
    'bathrooms',
    'bedrooms',
    'owner_monthly_costs',
    'property_code',
    'supportemail',
    'parent_id',
    'property_image_url',
    'deleted',
])]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'deleted' => 'boolean',
            'inactive_date' => 'date',
            'contract_start_date' => 'date',
            'contract_end_date' => 'date',
            'ac_management_fee' => 'decimal:2',
            'owner_monthly_costs' => 'decimal:2',
            'created_date' => 'datetime',
            'modified_date' => 'datetime',
        ];
    }

    /**
     * Qualifying live property for owner active access (ADR-009).
     */
    public function isLive(): bool
    {
        return $this->status === true && $this->deleted !== true;
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * @return BelongsTo<Subregion, $this>
     */
    public function subregion(): BelongsTo
    {
        return $this->belongsTo(Subregion::class);
    }

    /**
     * @return BelongsToMany<Owner, $this>
     */
    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(Owner::class, 'owners_properties');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query
            ->where('status', true)
            ->where(function (Builder $builder): void {
                $builder->where('deleted', false)->orWhereNull('deleted');
            });
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
