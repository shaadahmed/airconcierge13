<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Import\ImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function __construct(private ImportService $importService) {}

    public function importOwners(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $validated = $request->validate([
            'rows' => ['required', 'array'],
            'rows.*.owner_email' => ['nullable', 'email'],
            'rows.*.full_name' => ['nullable', 'string'],
            'rows.*.first_name' => ['nullable', 'string'],
            'rows.*.last_name' => ['nullable', 'string'],
            'rows.*.owner_phone' => ['nullable', 'string'],
            'rows.*.region_id' => ['nullable', 'integer'],
        ]);

        return response()->json([
            'imported' => $this->importService->importOwners($validated['rows']),
        ]);
    }

    public function importProperties(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $validated = $request->validate([
            'rows' => ['required', 'array'],
            'rows.*.property_title' => ['required', 'string'],
            'rows.*.region_id' => ['nullable', 'integer'],
            'rows.*.street_address' => ['nullable', 'string'],
            'rows.*.city' => ['nullable', 'string'],
            'rows.*.state' => ['nullable', 'string'],
            'rows.*.zipcode' => ['nullable', 'string'],
            'rows.*.hostaway_listing_id' => ['nullable', 'integer'],
        ]);

        return response()->json([
            'imported' => $this->importService->importProperties($validated['rows']),
        ]);
    }

    public function importedEmails(): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        return response()->json(['data' => $this->importService->listImportedEmails()]);
    }

    public function storeImportedEmail(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $validated = $request->validate([
            'source' => ['nullable', 'string', 'max:100'],
            'subject' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'from_email' => ['nullable', 'email'],
        ]);

        return response()->json([
            'data' => $this->importService->storeImportedEmail($validated),
        ], 201);
    }
}
