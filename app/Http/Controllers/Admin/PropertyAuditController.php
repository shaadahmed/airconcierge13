<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;

class PropertyAuditController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Property::class);
        return response()->json(['data' => Property::query()->notDeleted()
            ->orderByDesc('modified_date')->get(['id', 'property_title', 'status', 'modified_date'])
            ->map(fn (Property $property) => ['id' => $property->id, 'property_title' => $property->property_title, 'status' => $property->status, 'updated_at' => $property->modified_date])]);
    }
}
