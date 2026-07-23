<?php

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\PaymentReceipt;
use App\Models\PaymentType;
use App\Models\Property;
use App\Models\PropertyPayment;
use App\Models\PropertyPaymentType;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates updates and soft-deletes booking payments', function (): void {
    $booking = Booking::factory()->create();
    $type = PaymentType::factory()->create();
    $service = app(PaymentService::class);

    $payment = $service->createBookingPayment([
        'booking_id' => $booking->id,
        'payment_type_id' => $type->id,
        'amount' => 125.50,
        'payment_date' => '2026-07-01',
    ]);

    $updated = $service->updateBookingPayment($payment, ['amount' => 130.00]);
    $deleted = $service->deleteBookingPayment($updated);

    expect($updated->amount)->toBe('130.00')
        ->and($deleted->isDeleted())->toBeTrue();
});

it('records a receipt path for a booking payment', function (): void {
    $payment = BookingPayment::factory()->create();

    $receipt = app(PaymentService::class)->recordReceiptPath($payment, 'receipts/test.pdf');

    expect($receipt)->toBeInstanceOf(PaymentReceipt::class)
        ->and($receipt->receipt_path)->toBe('receipts/test.pdf')
        ->and($payment->fresh()->receipt?->receipt_path)->toBe('receipts/test.pdf');
});

it('creates and soft-deletes property payments', function (): void {
    $property = Property::factory()->create();
    $type = PropertyPaymentType::factory()->create();
    $service = app(PaymentService::class);

    $payment = $service->createPropertyPayment([
        'property_id' => $property->id,
        'property_payment_type_id' => $type->id,
        'amount' => 40.00,
    ]);

    $deleted = $service->deletePropertyPayment($payment);

    expect($payment)->toBeInstanceOf(PropertyPayment::class)
        ->and($deleted->isDeleted())->toBeTrue();
});
