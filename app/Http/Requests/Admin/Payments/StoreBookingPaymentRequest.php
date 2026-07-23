<?php

namespace App\Http\Requests\Admin\Payments;

use App\Models\BookingPayment;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BookingPayment::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'payment_type_id' => ['nullable', 'integer', 'exists:payment_types,id'],
            'amount' => ['required', 'numeric'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'receipt_path' => ['nullable', 'string', 'max:500'],
        ];
    }
}
