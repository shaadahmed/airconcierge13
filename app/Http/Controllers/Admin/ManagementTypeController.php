<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManagementType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagementTypeController extends Controller
{
    public function __construct() { $this->authorizeResource(ManagementType::class, 'managementType'); }
    public function index(): JsonResponse { return response()->json(['data' => ManagementType::query()->orderBy('name')->get()]); }
    public function store(Request $request): JsonResponse { $this->authorize('create', ManagementType::class); return response()->json(['data' => ManagementType::query()->create($this->validated($request))], 201); }
    public function update(Request $request, ManagementType $managementType): JsonResponse { $this->authorize('update', $managementType); $managementType->update($this->validated($request)); return response()->json(['data' => $managementType->fresh()]); }
    public function destroy(ManagementType $managementType): JsonResponse { $this->authorize('delete', $managementType); $managementType->delete(); return response()->json(['data' => ['id' => $managementType->id, 'deleted' => true]]); }
    /** @return array<string, mixed> */
    private function validated(Request $request): array { return $request->validate(['name' => ['required', 'string', 'max:255']]); }
}
