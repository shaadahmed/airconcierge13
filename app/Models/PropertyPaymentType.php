<?php

namespace App\Models;

use Database\Factories\PropertyPaymentTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'deleted'])]
class PropertyPaymentType extends Model
{
    /** @use HasFactory<PropertyPaymentTypeFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deleted' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PropertyPaymentTypeFee, $this>
     */
    public function fees(): HasMany
    {
        return $this->hasMany(PropertyPaymentTypeFee::class);
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
