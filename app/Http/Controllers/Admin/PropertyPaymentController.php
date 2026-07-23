<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PropertyPayments\StorePropertyPaymentRequest;
use App\Models\PropertyPayment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyPaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(Request $request): View|JsonResponse
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

    public function store(StorePropertyPaymentRequest $request): RedirectResponse|JsonResponse
    {
        $payment = $this->paymentService->createPropertyPayment($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['data' => $payment], 201);
        }

        return redirect()
            ->route('admin.property-payments.index')
            ->with('status', 'Property payment created.');
    }

    public function destroy(PropertyPayment $payment): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $payment);

        $this->paymentService->deletePropertyPayment($payment);

        if (request()->wantsJson()) {
            return response()->json(status: 204);
        }

        return back()->with('status', 'Property payment deleted.');
    }
}
