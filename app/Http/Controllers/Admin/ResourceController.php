<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function __construct() { $this->authorizeResource(Resource::class, 'resource'); }
    public function index(): JsonResponse { return response()->json(['data' => Resource::query()->with('children')->orderBy('order')->get()]); }
    public function store(Request $request): JsonResponse { $this->authorize('create', Resource::class); return response()->json(['data' => Resource::query()->create($this->validated($request))], 201); }
    public function update(Request $request, Resource $resource): JsonResponse { $this->authorize('update', $resource); $resource->update($this->validated($request)); return response()->json(['data' => $resource->fresh('children')]); }
    public function destroy(Resource $resource): JsonResponse { $this->authorize('delete', $resource); $resource->update(['deleted' => true]); return response()->json(['data' => $resource->fresh()]); }
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'path' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'link' => ['sometimes', 'boolean'],
            'external_link' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer'],
            'icon_class' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['parent_id'] = (int) ($validated['parent_id'] ?? 0);

        return $validated;
    }
}
