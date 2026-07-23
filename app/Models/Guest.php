<?php

namespace App\Models;

use Database\Factories\GuestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['guest_name', 'first_name', 'last_name', 'phone', 'email', 'city', 'state', 'country', 'blacklisted', 'notes', 'deleted'])]
class Guest extends Model
{
    /** @use HasFactory<GuestFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return ['blacklisted' => 'boolean', 'deleted' => 'boolean'];
    }

    /** @return BelongsToMany<Booking, $this> */
    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'guests_bookings');
    }
}
