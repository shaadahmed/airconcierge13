<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['region_id', 'subregion_id', 'property_id', 'booking_code', 'month', 'year', 'no_of_nights', 'booking_date', 'reservation_start_date', 'reservation_end_date', 'checkin_time', 'checkout_time', 'no_of_guests', 'platform_id', 'hostaway_reservation_id', 'accomodations', 'cleaning_fee', 'tot_charged_to_guest', 'total_guest_paid', 'management_fee', 'owner_payout_amount_from_airconcierge', 'cancelled_booking', 'owner_notes', 'booking_notes', 'airbnb_reservation_code', 'deleted', 'is_payment_clear', 'dateadded'])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return ['booking_date' => 'date', 'reservation_start_date' => 'date', 'reservation_end_date' => 'date', 'dateadded' => 'datetime', 'cancelled_booking' => 'boolean', 'deleted' => 'boolean', 'is_payment_clear' => 'boolean'];
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_booking === true;
    }

    public function isDeleted(): bool
    {
        return $this->deleted === true;
    }

    /** @return BelongsTo<Property, $this> */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /** @return BelongsTo<Platform, $this> */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    /** @return BelongsToMany<Guest, $this> */
    public function guests(): BelongsToMany
    {
        return $this->belongsToMany(Guest::class, 'guests_bookings');
    }

    /** @return HasMany<BookingPayment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(BookingPayment::class);
    }

    /** @return HasMany<BookingIncomingPayment, $this> */
    public function incomingPayments(): HasMany
    {
        return $this->hasMany(BookingIncomingPayment::class);
    }

    /** @param Builder<self> $query @return Builder<self> */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where(fn (Builder $query) => $query->where('deleted', false)->orWhereNull('deleted'));
    }
}
