<?php

namespace App\Models;

use Database\Factories\BookingIncomingPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['booking_id', 'incoming_payment_type_id', 'amount', 'payment_date', 'notes', 'deleted'])]
class BookingIncomingPayment extends Model
{
    /** @use HasFactory<BookingIncomingPaymentFactory> */
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
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * @return BelongsTo<IncomingPaymentType, $this>
     */
    public function incomingPaymentType(): BelongsTo
    {
        return $this->belongsTo(IncomingPaymentType::class);
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
