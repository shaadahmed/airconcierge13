<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RevenueSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RevenueSettingsController extends Controller
{
    public function __construct() { $this->authorizeResource(RevenueSetting::class, 'revenueSetting'); }
    public function index(): JsonResponse { return response()->json(['data' => RevenueSetting::query()->with('property')->orderByDesc('id')->get()]); }
    public function store(Request $request): JsonResponse { $this->authorize('create', RevenueSetting::class); return response()->json(['data' => RevenueSetting::query()->create($this->validated($request))], 201); }
    public function update(Request $request, RevenueSetting $revenueSetting): JsonResponse { $this->authorize('update', $revenueSetting); $revenueSetting->update($this->validated($request)); return response()->json(['data' => $revenueSetting->fresh('property')]); }
    /** @return array<string, mixed> */
    private function validated(Request $request): array { return $request->validate([
        'property_id' => ['required', 'integer', 'exists:properties,id'], 'dynamic_pricing' => ['nullable', 'integer'],
        'base_rate' => ['nullable', 'numeric'], 'min_rate' => ['nullable', 'numeric'],
    ]); }
}
