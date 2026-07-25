<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Imports\ImportOwnersRequest;
use App\Http\Requests\Admin\Imports\ImportPropertiesRequest;
use App\Http\Requests\Admin\Imports\StoreImportedEmailRequest;
use App\Models\Booking;
use App\Services\Import\ImportService;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    public function __construct(private ImportService $importService) {}

    public function importOwners(ImportOwnersRequest $request): JsonResponse
    {
        return response()->json([
            'imported' => $this->importService->importOwners($request->validated('rows')),
        ]);
    }

    public function importProperties(ImportPropertiesRequest $request): JsonResponse
    {
        return response()->json([
            'imported' => $this->importService->importProperties($request->validated('rows')),
        ]);
    }

    public function importedEmails(): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        return response()->json(['data' => $this->importService->listImportedEmails()]);
    }

    public function storeImportedEmail(StoreImportedEmailRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->importService->storeImportedEmail($request->validated()),
        ], 201);
    }
}
