<?php

namespace App\Models;

use Database\Factories\PropertyPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['property_id', 'property_payment_type_id', 'amount', 'payment_date', 'notes', 'deleted'])]
class PropertyPayment extends Model
{
    /** @use HasFactory<PropertyPaymentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'deleted' => 'boolean',
        ];
    }

    public function isDeleted(): bool
    {
        return $this->deleted === true;
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * @return BelongsTo<PropertyPaymentType, $this>
     */
    public function propertyPaymentType(): BelongsTo
    {
        return $this->belongsTo(PropertyPaymentType::class);
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
