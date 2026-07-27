<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{
    public function __construct() { $this->authorizeResource(PaymentType::class, 'paymentType'); }
    public function index(): JsonResponse { return response()->json(['data' => PaymentType::query()->notDeleted()->orderBy('name')->get()]); }
    public function store(Request $request): JsonResponse { $this->authorize('create', PaymentType::class); return response()->json(['data' => PaymentType::query()->create($this->validated($request))], 201); }
    public function update(Request $request, PaymentType $paymentType): JsonResponse { $this->authorize('update', $paymentType); $paymentType->update($this->validated($request)); return response()->json(['data' => $paymentType->fresh()]); }
    public function destroy(PaymentType $paymentType): JsonResponse { $this->authorize('delete', $paymentType); $paymentType->update(['deleted' => true]); return response()->json(['data' => $paymentType->fresh()]); }
    /** @return array<string, mixed> */
    private function validated(Request $request): array { return $request->validate(['name' => ['required', 'string', 'max:255']]); }
}
