<?php

namespace App\Models;

use App\Enums\BookingLimitType;
use Database\Factories\SubregionBookingLimitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['subregion_id', 'limit_type', 'limit_value', 'limit_filter'])]

class SubregionBookingLimit extends Model
{
    /** @use HasFactory<SubregionBookingLimitFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsTo<Subregion, $this>
     */
    public function subregion(): BelongsTo
    {
        return $this->belongsTo(Subregion::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'limit_type' => BookingLimitType::class,
            'limit_value' => 'integer',
        ];
    }
}
