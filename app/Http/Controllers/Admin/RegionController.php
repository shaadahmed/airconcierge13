<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Regions\StoreRegionRequest;
use App\Http\Requests\Admin\Regions\UpdateRegionRequest;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class RegionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Region::class, 'region');
    }

    public function index(): JsonResponse
    {
        $regions = Region::query()
            ->with('subregions')
            ->orderBy('name')
            ->get()
            ->map(fn (Region $region) => $this->toAdminArray($region));

        return response()->json(['data' => $regions]);
    }

    public function store(StoreRegionRequest $request): JsonResponse
    {
        $region = Region::query()->create($request->validated());

        return response()->json(['data' => $this->toAdminArray($region)], 201);
    }

    public function update(UpdateRegionRequest $request, Region $region): JsonResponse
    {
        $region->update($request->validated());
        $region = $region->fresh('subregions') ?? $region;

        return response()->json(['data' => $this->toAdminArray($region)]);
    }

    public function destroy(Region $region): JsonResponse
    {
        $region->subregions()->each(function ($subregion): void {
            $subregion->bookingLimits()->each(fn ($limit) => $limit->delete());
            $subregion->permits()->each(fn ($permit) => $permit->delete());
            $subregion->delete();
        });
        $region->delete();

        return response()->json(['data' => ['id' => $region->id, 'deleted' => true]]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toAdminArray(Region $region): array
    {
        /** @var Collection<int, \App\Models\Subregion> $subregions */
        $subregions = $region->relationLoaded('subregions')
            ? $region->subregions
            : collect();

        return [
            'id' => $region->id,
            'name' => $region->name,
            'code' => $region->code,
            'color' => $region->color,
            'subregions' => $subregions->map(fn ($subregion) => [
                'id' => $subregion->id,
                'region_id' => $subregion->region_id,
                'name' => $subregion->name,
            ])->values(),
        ];
    }
}
