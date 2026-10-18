<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Regions\StoreSubregionRequest;
use App\Http\Requests\Admin\Regions\UpdateSubregionRequest;
use App\Models\Subregion;
use App\Services\Regions\SubregionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubregionController extends Controller
{
    public function __construct(private SubregionService $subregions)
    {
        $this->authorizeResource(Subregion::class, 'subregion');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Subregion::query()
            ->with(['region', 'bookingLimits', 'permits'])
            ->orderBy('name');

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        $rows = $query->get()->map(fn (Subregion $subregion) => $subregion->toAdminArray());

        return response()->json(['data' => $rows]);
    }

    public function store(StoreSubregionRequest $request): JsonResponse
    {
        $subregion = $this->subregions->create($request->validated());

        return response()->json(['data' => $subregion->toAdminArray()], 201);
    }

    public function update(UpdateSubregionRequest $request, Subregion $subregion): JsonResponse
    {
        $subregion = $this->subregions->update($subregion, $request->validated());

        return response()->json(['data' => $subregion->toAdminArray()]);
    }

    public function destroy(Subregion $subregion): JsonResponse
    {
        $id = $subregion->id;
        $this->subregions->delete($subregion);

        return response()->json(['data' => ['id' => $id, 'deleted' => true]]);
    }
}
