<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Payments\StoreBookingPaymentRequest;
use App\Models\BookingPayment;
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
            // Receipt PDF rendering is Phase 4; only the path is stored now.
            $this->paymentService->recordReceiptPath($payment, $receiptPath);
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
}
