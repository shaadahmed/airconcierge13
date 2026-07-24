<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Payments\StoreBookingPaymentRequest;
use App\Http\Requests\Admin\PropertyPayments\StorePropertyPaymentRequest;
use App\Models\BookingPayment;
use App\Models\PropertyPayment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BookingPayment::class);

        $payments = $this->paymentService->listBookingPayments(
            $request->integer('booking_id') ?: null,
        );

        return response()->json(['data' => $payments]);
    }

    public function store(StoreBookingPaymentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $receiptPath = $validated['receipt_path'] ?? null;
        unset($validated['receipt_path']);

        $payment = $this->paymentService->createBookingPayment($validated);

        if (is_string($receiptPath) && $receiptPath !== '') {
            $this->paymentService->recordReceiptPath($payment, $receiptPath);
        } else {
            $this->paymentService->queuePaymentReceiptPdf($payment);
        }

        return response()->json(['data' => $payment->load('receipt')], 201);
    }

    public function destroy(BookingPayment $payment): JsonResponse
    {
        $this->authorize('delete', $payment);

        $this->paymentService->deleteBookingPayment($payment);

        return response()->json(status: 204);
    }

    public function propertyIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PropertyPayment::class);

        $payments = $this->paymentService->listPropertyPayments(
            $request->integer('property_id') ?: null,
        );

        return response()->json(['data' => $payments]);
    }

    public function propertyStore(StorePropertyPaymentRequest $request): JsonResponse
    {
        $payment = $this->paymentService->createPropertyPayment($request->validated());

        return response()->json(['data' => $payment], 201);
    }

    public function propertyDestroy(PropertyPayment $propertyPayment): JsonResponse
    {
        $this->authorize('delete', $propertyPayment);

        $this->paymentService->deletePropertyPayment($propertyPayment);

        return response()->json(status: 204);
    }
}
