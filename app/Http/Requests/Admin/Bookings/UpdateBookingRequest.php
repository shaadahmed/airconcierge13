<?php

namespace App\Http\Requests\Admin\Bookings;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Booking|null $booking */
        $booking = $this->route('booking');

        return $booking !== null && ($this->user()?->can('update', $booking) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'property_id' => ['sometimes', 'integer', 'exists:properties,id'],
            'platform_id' => ['nullable', 'integer', 'exists:platforms,id'],
            'booking_code' => ['nullable', 'string', 'max:255'],
            'reservation_start_date' => ['nullable', 'date'],
            'reservation_end_date' => ['nullable', 'date', 'after_or_equal:reservation_start_date'],
            'booking_date' => ['nullable', 'date'],
            'no_of_guests' => ['nullable', 'integer', 'min:1'],
            'owner_notes' => ['nullable', 'string'],
            'booking_notes' => ['nullable', 'string'],
            'guest_ids' => ['nullable', 'array'],
            'guest_ids.*' => ['integer', 'exists:guests,id'],
        ];
    }
}
