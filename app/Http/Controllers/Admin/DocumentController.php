<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Documents\StoreDocumentRequest;
use App\Http\Requests\Admin\Documents\UpdateDocumentRequest;
use App\Models\Booking;
use App\Models\DocumentUpload;
use App\Services\Documents\DocumentService;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    public function __construct(private DocumentService $documentService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        return response()->json(['data' => $this->documentService->list()]);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $document = $this->documentService->create($request->validated(), $request->file('file'));

        return response()->json(['data' => $document], 201);
    }

    public function update(UpdateDocumentRequest $request, DocumentUpload $document): JsonResponse
    {
        return response()->json(['data' => $this->documentService->update($document, $request->validated())]);
    }

    public function destroy(DocumentUpload $document): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);
        $this->documentService->delete($document);

        return response()->json(status: 204);
    }
}
