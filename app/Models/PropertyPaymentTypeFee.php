<?php

namespace App\Models;

use Database\Factories\PropertyPaymentTypeFeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['property_payment_type_id', 'fee_amount', 'fee_label'])]
class PropertyPaymentTypeFee extends Model
{
    /** @use HasFactory<PropertyPaymentTypeFeeFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fee_amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<PropertyPaymentType, $this>
     */
    public function propertyPaymentType(): BelongsTo
    {
        return $this->belongsTo(PropertyPaymentType::class);
    }
}
