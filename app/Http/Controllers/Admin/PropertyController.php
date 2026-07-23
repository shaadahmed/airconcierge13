<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Services\Properties\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(private PropertyService $propertyService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        return response()->json([
            'data' => $this->propertyService->list($request->only(['region_id', 'status'])),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $validated = $request->validate([
            'property_title' => ['required', 'string', 'max:255'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'subregion_id' => ['nullable', 'integer', 'exists:subregions,id'],
            'hostaway_listing_id' => ['nullable', 'integer'],
            'street_address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'zipcode' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
            'owner_ids' => ['nullable', 'array'],
            'owner_ids.*' => ['integer', 'exists:owners,id'],
        ]);

        return response()->json([
            'data' => $this->propertyService->create($validated),
        ], 201);
    }

    public function update(Request $request, Property $property): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $validated = $request->validate([
            'property_title' => ['sometimes', 'required', 'string', 'max:255'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'subregion_id' => ['nullable', 'integer', 'exists:subregions,id'],
            'hostaway_listing_id' => ['nullable', 'integer'],
            'street_address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'zipcode' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
            'owner_ids' => ['nullable', 'array'],
            'owner_ids.*' => ['integer', 'exists:owners,id'],
        ]);

        return response()->json([
            'data' => $this->propertyService->update($property, $validated),
        ]);
    }

    public function destroy(Property $property): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        return response()->json([
            'data' => $this->propertyService->delete($property),
        ]);
    }
}
