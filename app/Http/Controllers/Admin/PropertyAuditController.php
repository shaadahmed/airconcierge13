<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyAuditController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Property::class);

        return response()->json(['data' => Property::query()->notDeleted()
            ->orderByDesc('modified_date')->get(['id', 'property_title', 'status', 'modified_date'])
            ->map(fn (Property $property) => ['id' => $property->id, 'property_title' => $property->property_title, 'status' => $property->status, 'updated_at' => $property->modified_date])]);
    }

    /**
     * Accept create payloads from the SPA form.
     * Persistence waits on the property_audit_reports schema port.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Property::class);

        $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'visitor_name' => ['nullable', 'string', 'max:255'],
            'visit_date' => ['nullable', 'date'],
            'visit_purpose' => ['nullable', 'string'],
            'work_performed' => ['nullable', 'string'],
            'guest_owner_notes' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'hourly_work' => ['sometimes', 'boolean'],
            'no_of_hours' => ['nullable', 'integer', 'min:0'],
            'materials_cost' => ['nullable', 'integer', 'min:0'],
        ]);

        return response()->json([
            'message' => 'Property audit report persistence is not available yet (schema not ported).',
        ], 501);
    }
}
