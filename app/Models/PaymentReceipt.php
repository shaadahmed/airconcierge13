<?php

namespace App\Models;

use Database\Factories\PaymentReceiptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['booking_payment_id', 'receipt_path'])]
class PaymentReceipt extends Model
{
    /** @use HasFactory<PaymentReceiptFactory> */
    use HasFactory;

    // Payment receipt PDF generation is deferred to Phase 4 (ADR-005 / GeneratePdfJob).

    /**
     * @return BelongsTo<BookingPayment, $this>
     */
    public function bookingPayment(): BelongsTo
    {
        return $this->belongsTo(BookingPayment::class);
    }
}
