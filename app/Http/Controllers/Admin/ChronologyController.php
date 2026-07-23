<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Chronology\StoreChronologyOrderRequest;
use App\Http\Requests\Admin\Chronology\StoreChronologyRequest;
use App\Http\Requests\Admin\Chronology\UpdateChronologyRequest;
use App\Models\Chronology;
use App\Models\ChronologyOrder;
use App\Models\DocumentUpload;
use App\Models\EmailTemplate;
use App\Services\Chronology\ChronologyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChronologyController extends Controller
{
    public function __construct(private ChronologyService $chronologyService) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Chronology::class);

        $chronologies = $this->chronologyService->list();

        if ($request->wantsJson()) {
            return response()->json(['data' => $chronologies]);
        }

        return view('admin.chronologies.index', compact('chronologies'));
    }

    public function create(): View
    {
        $this->authorize('create', Chronology::class);

        return view('admin.chronologies.create');
    }

    public function store(StoreChronologyRequest $request): RedirectResponse|JsonResponse
    {
        $chronology = $this->chronologyService->create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['data' => $chronology], 201);
        }

        return redirect()
            ->route('admin.chronologies.show', $chronology)
            ->with('status', 'Chronology created.');
    }

    public function show(Chronology $chronology): View|JsonResponse
    {
        $this->authorize('view', $chronology);

        $chronology->load(['orders', 'regions', 'subregions']);

        if (request()->wantsJson()) {
            return response()->json(['data' => $chronology]);
        }

        return view('admin.chronologies.show', compact('chronology'));
    }

    public function edit(Chronology $chronology): View
    {
        $this->authorize('update', $chronology);

        return view('admin.chronologies.edit', compact('chronology'));
    }

    public function update(UpdateChronologyRequest $request, Chronology $chronology): RedirectResponse|JsonResponse
    {
        $chronology = $this->chronologyService->update($chronology, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['data' => $chronology]);
        }

        return redirect()
            ->route('admin.chronologies.show', $chronology)
            ->with('status', 'Chronology updated.');
    }

    public function destroy(Chronology $chronology): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $chronology);

        $this->chronologyService->delete($chronology);

        if (request()->wantsJson()) {
            return response()->json(status: 204);
        }

        return redirect()
            ->route('admin.chronologies.index')
            ->with('status', 'Chronology deleted.');
    }

    public function copy(Chronology $chronology): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Chronology::class);

        $copy = $this->chronologyService->copy($chronology);

        if (request()->wantsJson()) {
            return response()->json(['data' => $copy], 201);
        }

        return redirect()
            ->route('admin.chronologies.show', $copy)
            ->with('status', 'Chronology copied.');
    }

    public function templates(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Chronology::class);

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
        $this->authorize('viewAny', Chronology::class);

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
        $this->authorize('viewAny', Chronology::class);

        $exists = Chronology::query()
            ->where('name', $request->string('name')->toString())
            ->when($request->integer('ignore_id'), fn ($q, int $id) => $q->where('id', '!=', $id))
            ->exists();

        return response()->json(['status' => $exists ? 'exist' : 'notexist']);
    }

    public function storeOrder(StoreChronologyOrderRequest $request, Chronology $chronology): RedirectResponse|JsonResponse
    {
        $order = $this->chronologyService->createOrder($chronology, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['data' => $order], 201);
        }

        return back()->with('status', 'Order step created.');
    }

    public function updateOrder(StoreChronologyOrderRequest $request, Chronology $chronology, ChronologyOrder $order): RedirectResponse|JsonResponse
    {
        abort_unless($order->chronology_id === $chronology->id, 404);

        $order = $this->chronologyService->updateOrder($order, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['data' => $order]);
        }

        return back()->with('status', 'Order step updated.');
    }

    public function destroyOrder(Chronology $chronology, ChronologyOrder $order): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $chronology);
        abort_unless($order->chronology_id === $chronology->id, 404);

        $this->chronologyService->deleteOrder($order);

        if (request()->wantsJson()) {
            return response()->json(status: 204);
        }

        return back()->with('status', 'Order step deleted.');
    }

    public function previewOwners(Chronology $chronology): JsonResponse
    {
        $this->authorize('view', $chronology);

        return response()->json(['data' => $this->chronologyService->previewOwners($chronology)]);
    }

    public function storeOwnerEmails(Request $request, Chronology $chronology): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $chronology);

        $validated = $request->validate([
            'owner_ids' => ['nullable', 'array'],
            'owner_ids.*' => ['integer', 'exists:owners,id'],
        ]);

        $this->chronologyService->saveOwnerOptOuts($chronology, $validated['owner_ids'] ?? []);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('status', 'Owner opt-outs saved.');
    }
}
