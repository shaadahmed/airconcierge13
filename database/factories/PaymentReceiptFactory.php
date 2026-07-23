<?php

namespace Database\Factories;

use App\Models\BookingPayment;
use App\Models\PaymentReceipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentReceipt>
 */
class PaymentReceiptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_payment_id' => BookingPayment::factory(),
            'receipt_path' => 'receipts/'.fake()->uuid().'.pdf',
        ];
    }
}
