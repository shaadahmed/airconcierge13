<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Chronology\StoreChronologyOrderRequest;
use App\Http\Requests\Admin\Chronology\StoreChronologyOwnerEmailsRequest;
use App\Http\Requests\Admin\Chronology\StoreChronologyRequest;
use App\Http\Requests\Admin\Chronology\UpdateChronologyRequest;
use App\Models\Chronology;
use App\Models\ChronologyOrder;
use App\Models\DocumentUpload;
use App\Models\EmailTemplate;
use App\Services\Chronology\ChronologyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChronologyController extends Controller
{
    public function __construct(private ChronologyService $chronologyService)
    {
        $this->authorizeResource(Chronology::class, 'chronology');
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->chronologyService->list()]);
    }

    public function create(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function store(StoreChronologyRequest $request): JsonResponse
    {
        $chronology = $this->chronologyService->create($request->validated());

        return response()->json(['data' => $chronology], 201);
    }

    public function show(Chronology $chronology): JsonResponse
    {
        $chronology->load(['orders', 'regions', 'subregions']);

        return response()->json(['data' => $chronology]);
    }

    public function edit(Chronology $chronology): JsonResponse
    {
        $chronology->load(['orders', 'regions', 'subregions']);

        return response()->json(['data' => $chronology]);
    }

    public function update(UpdateChronologyRequest $request, Chronology $chronology): JsonResponse
    {
        $chronology = $this->chronologyService->update($chronology, $request->validated());

        return response()->json(['data' => $chronology]);
    }

    public function destroy(Chronology $chronology): JsonResponse
    {
        $this->chronologyService->delete($chronology);

        return response()->json(status: 204);
    }

    public function copy(Chronology $chronology): JsonResponse
    {
        $copy = $this->chronologyService->copy($chronology);

        return response()->json(['data' => $copy], 201);
    }

    public function templates(Request $request): JsonResponse
    {
        $templates = EmailTemplate::query()
            ->when($request->integer('region_id'), function ($query, int $regionId): void {
                $query->whereHas('regions', fn ($q) => $q->where('regions.id', $regionId));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'templatesubject']);

        return response()->json(['data' => $templates]);
    }

    public function documents(Request $request): JsonResponse
    {
        $documents = DocumentUpload::query()
            ->when($request->integer('region_id'), function ($query, int $regionId): void {
                $query->whereHas('regions', fn ($q) => $q->where('regions.id', $regionId));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'document', 'signid']);

        return response()->json(['data' => $documents]);
    }

    public function checkName(Request $request): JsonResponse
    {
        $exists = Chronology::query()
            ->where('name', $request->string('name')->toString())
            ->when($request->integer('ignore_id'), fn ($q, int $id) => $q->where('id', '!=', $id))
            ->exists();

        return response()->json(['status' => $exists ? 'exist' : 'notexist']);
    }

    public function storeOrder(StoreChronologyOrderRequest $request, Chronology $chronology): JsonResponse
    {
        $order = $this->chronologyService->createOrder($chronology, $request->validated());

        return response()->json(['data' => $order], 201);
    }

    public function updateOrder(StoreChronologyOrderRequest $request, Chronology $chronology, ChronologyOrder $order): JsonResponse
    {
        abort_unless($order->chronology_id === $chronology->id, 404);

        $order = $this->chronologyService->updateOrder($order, $request->validated());

        return response()->json(['data' => $order]);
    }

    public function destroyOrder(Chronology $chronology, ChronologyOrder $order): JsonResponse
    {
        abort_unless($order->chronology_id === $chronology->id, 404);

        $this->chronologyService->deleteOrder($order);

        return response()->json(status: 204);
    }

    public function previewOwners(Chronology $chronology): JsonResponse
    {
        return response()->json(['data' => $this->chronologyService->previewOwners($chronology)]);
    }

    public function storeOwnerEmails(StoreChronologyOwnerEmailsRequest $request, Chronology $chronology): JsonResponse
    {
        $this->chronologyService->saveOwnerOptOuts($chronology, $request->validated('owner_ids') ?? []);

        return response()->json(['status' => 'ok']);
    }
}
