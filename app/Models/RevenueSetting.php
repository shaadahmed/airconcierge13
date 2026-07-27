<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['property_id', 'dynamic_pricing', 'base_rate', 'min_rate'])]
class RevenueSetting extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'property_id' => 'integer',
            'dynamic_pricing' => 'integer',
            'base_rate' => 'decimal:2',
            'min_rate' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Property, $this> */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
