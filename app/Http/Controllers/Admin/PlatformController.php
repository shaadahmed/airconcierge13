<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function __construct() { $this->authorizeResource(Platform::class, 'platform'); }
    public function index(): JsonResponse { return response()->json(['data' => Platform::query()->notDeleted()->orderBy('platform_name')->get()]); }
    public function store(Request $request): JsonResponse { $this->authorize('create', Platform::class); return response()->json(['data' => Platform::query()->create($this->validated($request))], 201); }
    public function update(Request $request, Platform $platform): JsonResponse { $this->authorize('update', $platform); $platform->update($this->validated($request)); return response()->json(['data' => $platform->fresh()]); }
    public function destroy(Platform $platform): JsonResponse { $this->authorize('delete', $platform); $platform->update(['deleted' => true]); return response()->json(['data' => $platform->fresh()]); }
    /** @return array<string, mixed> */
    private function validated(Request $request): array { return $request->validate(['platform_name' => ['required', 'string', 'max:255']]); }
}
