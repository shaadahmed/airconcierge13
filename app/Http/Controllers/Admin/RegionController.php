<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Region::class, 'region');
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => Region::query()->notDeleted()->with('subregions')->orderBy('region_name')->get()]);
    }

    public function subregions(Region $region): JsonResponse
    {
        $this->authorize('view', $region);

        return response()->json(['data' => $region->subregions()->notDeleted()->orderBy('subregion_name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Region::class);
        $region = Region::query()->create($this->validated($request));

        return response()->json(['data' => $region], 201);
    }

    public function update(Request $request, Region $region): JsonResponse
    {
        $this->authorize('update', $region);
        $region->update($this->validated($request));

        return response()->json(['data' => $region->fresh()]);
    }

    public function destroy(Region $region): JsonResponse
    {
        $this->authorize('delete', $region);
        $region->update(['deleted' => true]);

        return response()->json(['data' => $region->fresh()]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'region_name' => ['required', 'string', 'max:255'],
            'shortcode' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:100'],
        ]);
    }
}
