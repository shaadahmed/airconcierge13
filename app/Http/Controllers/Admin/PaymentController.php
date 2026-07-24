<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Payments\StoreBookingPaymentRequest;
use App\Http\Requests\Admin\PropertyPayments\StorePropertyPaymentRequest;
use App\Models\BookingPayment;
use App\Models\PropertyPayment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', BookingPayment::class);

        $payments = $this->paymentService->listBookingPayments(
            $request->integer('booking_id') ?: null,
        );

        if ($request->wantsJson()) {
            return response()->json(['data' => $payments]);
        }

        return view('admin.payments.index', compact('payments'));
    }

    public function store(StoreBookingPaymentRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $receiptPath = $validated['receipt_path'] ?? null;
        unset($validated['receipt_path']);

        $payment = $this->paymentService->createBookingPayment($validated);

        if (is_string($receiptPath) && $receiptPath !== '') {
            $this->paymentService->recordReceiptPath($payment, $receiptPath);
        } else {
            // Sync path recording stays sync; PDF HTML→PDF is queued (Phase 4).
            $this->paymentService->queuePaymentReceiptPdf($payment);
        }

        if ($request->wantsJson()) {
            return response()->json(['data' => $payment->load('receipt')], 201);
        }

        return redirect()
            ->route('admin.payments.index')
            ->with('status', 'Booking payment created.');
    }

    public function destroy(BookingPayment $payment): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $payment);

        $this->paymentService->deleteBookingPayment($payment);

        if (request()->wantsJson()) {
            return response()->json(status: 204);
        }

        return back()->with('status', 'Booking payment deleted.');
    }

    public function propertyIndex(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', PropertyPayment::class);

        $payments = $this->paymentService->listPropertyPayments(
            $request->integer('property_id') ?: null,
        );

        if ($request->wantsJson()) {
            return response()->json(['data' => $payments]);
        }

        return view('admin.property-payments.index', compact('payments'));
    }

    public function propertyStore(StorePropertyPaymentRequest $request): RedirectResponse|JsonResponse
    {
        $payment = $this->paymentService->createPropertyPayment($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['data' => $payment], 201);
        }

        return redirect()
            ->route('admin.property-payments.index')
            ->with('status', 'Property payment created.');
    }

    public function propertyDestroy(PropertyPayment $propertyPayment): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $propertyPayment);

        $this->paymentService->deletePropertyPayment($propertyPayment);

        if (request()->wantsJson()) {
            return response()->json(status: 204);
        }

        return back()->with('status', 'Property payment deleted.');
    }
}
