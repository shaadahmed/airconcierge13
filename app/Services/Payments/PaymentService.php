<?php

namespace App\Services\Payments;

use App\Jobs\GeneratePdfJob;
use App\Models\BookingIncomingPayment;
use App\Models\BookingPayment;
use App\Models\PaymentReceipt;
use App\Models\PropertyPayment;
use Illuminate\Database\Eloquent\Collection;

class PaymentService
{
    /**
     * @return Collection<int, BookingPayment>
     */
    public function listBookingPayments(?int $bookingId = null): Collection
    {
        return BookingPayment::query()
            ->notDeleted()
            ->when($bookingId, fn ($query, int $id) => $query->where('booking_id', $id))
            ->latest('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createBookingPayment(array $attributes): BookingPayment
    {
        return BookingPayment::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateBookingPayment(BookingPayment $payment, array $attributes): BookingPayment
    {
        $payment->update($attributes);

        return $payment->fresh() ?? $payment;
    }

    public function deleteBookingPayment(BookingPayment $payment): BookingPayment
    {
        $payment->update(['deleted' => true]);

        return $payment->fresh() ?? $payment;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createIncomingPayment(array $attributes): BookingIncomingPayment
    {
        return BookingIncomingPayment::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateIncomingPayment(BookingIncomingPayment $payment, array $attributes): BookingIncomingPayment
    {
        $payment->update($attributes);

        return $payment->fresh() ?? $payment;
    }

    public function deleteIncomingPayment(BookingIncomingPayment $payment): BookingIncomingPayment
    {
        $payment->update(['deleted' => true]);

        return $payment->fresh() ?? $payment;
    }

    /**
     * @return Collection<int, PropertyPayment>
     */
    public function listPropertyPayments(?int $propertyId = null): Collection
    {
        return PropertyPayment::query()
            ->notDeleted()
            ->when($propertyId, fn ($query, int $id) => $query->where('property_id', $id))
            ->latest('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createPropertyPayment(array $attributes): PropertyPayment
    {
        return PropertyPayment::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updatePropertyPayment(PropertyPayment $payment, array $attributes): PropertyPayment
    {
        $payment->update($attributes);

        return $payment->fresh() ?? $payment;
    }

    public function deletePropertyPayment(PropertyPayment $payment): PropertyPayment
    {
        $payment->update(['deleted' => true]);

        return $payment->fresh() ?? $payment;
    }

    /**
     * Record a receipt file path for a booking payment.
     * Prefer dispatching GeneratePdfJob for HTML→PDF rendering (ADR-005).
     */
    public function recordReceiptPath(BookingPayment $payment, string $path): PaymentReceipt
    {
        return PaymentReceipt::query()->updateOrCreate(
            ['booking_payment_id' => $payment->id],
            ['receipt_path' => $path],
        );
    }

    /**
     * Queue payment receipt PDF generation (I/O-bound DomPDF work).
     */
    public function queuePaymentReceiptPdf(BookingPayment $payment): void
    {
        GeneratePdfJob::dispatch("payment-receipt:{$payment->id}");
    }
}
