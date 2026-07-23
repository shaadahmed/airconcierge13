<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['property_id', 'year', 'month', 'revenue', 'occupancy', 'nights_booked'])]
class PropertyMonthlyMetric extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'revenue' => 'decimal:2',
            'occupancy' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Property, $this> */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
