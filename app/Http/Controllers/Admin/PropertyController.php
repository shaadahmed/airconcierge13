<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Properties\StorePropertyRequest;
use App\Http\Requests\Admin\Properties\UpdatePropertyRequest;
use App\Models\Property;
use App\Services\Properties\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(private PropertyService $propertyService)
    {
        $this->authorizeResource(Property::class, 'property');
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->propertyService->list($request->only(['region_id', 'status'])),
        ]);
    }

    public function store(StorePropertyRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->propertyService->create($request->validated()),
        ], 201);
    }

    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    {
        return response()->json([
            'data' => $this->propertyService->update($property, $request->validated()),
        ]);
    }

    public function destroy(Property $property): JsonResponse
    {
        return response()->json([
            'data' => $this->propertyService->delete($property),
        ]);
    }
}
