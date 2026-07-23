<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\DocumentUpload;
use App\Services\Documents\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(private DocumentService $documentService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        return response()->json(['data' => $this->documentService->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ownerspecific' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'integer'],
            'signid' => ['nullable', 'string', 'max:255'],
            'zohoactionid' => ['nullable', 'string', 'max:255'],
            'roletitle' => ['nullable', 'string', 'max:255'],
            'region_ids' => ['nullable', 'array'],
            'region_ids.*' => ['integer'],
            'subregion_ids' => ['nullable', 'array'],
            'subregion_ids.*' => ['integer'],
            'owner_ids' => ['nullable', 'array'],
            'owner_ids.*' => ['integer'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $document = $this->documentService->create($validated, $request->file('file'));

        return response()->json(['data' => $document], 201);
    }

    public function update(Request $request, DocumentUpload $document): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'ownerspecific' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'integer'],
            'signid' => ['nullable', 'string', 'max:255'],
            'zohoactionid' => ['nullable', 'string', 'max:255'],
            'roletitle' => ['nullable', 'string', 'max:255'],
            'region_ids' => ['nullable', 'array'],
            'subregion_ids' => ['nullable', 'array'],
            'owner_ids' => ['nullable', 'array'],
        ]);

        return response()->json(['data' => $this->documentService->update($document, $validated)]);
    }

    public function destroy(DocumentUpload $document): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);
        $this->documentService->delete($document);

        return response()->json(status: 204);
    }
}
