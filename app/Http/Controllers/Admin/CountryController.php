<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Country::class);

        return response()->json([
            'data' => Country::query()
                ->with(['states' => fn ($query) => $query->notDeleted()->orderBy('name')])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Country::class);
        $country = Country::query()->create($this->countryValidated($request));

        return response()->json(['data' => $country->load('states')], 201);
    }

    public function update(Request $request, Country $country): JsonResponse
    {
        $this->authorize('update', $country);
        $country->update($this->countryValidated($request, $country->id));

        return response()->json(['data' => $country->fresh('states')]);
    }

    public function destroy(Country $country): JsonResponse
    {
        $this->authorize('delete', $country);
        $country->states()->update(['deleted' => true]);
        $country->delete();

        return response()->json(['data' => ['id' => $country->id, 'deleted' => true]]);
    }

    public function storeState(Request $request, Country $country): JsonResponse
    {
        $this->authorize('update', $country);
        $state = $country->states()->create($this->stateValidated($request));

        return response()->json(['data' => $state], 201);
    }

    public function updateState(Request $request, Country $country, State $state): JsonResponse
    {
        $this->authorize('update', $country);
        abort_unless((int) $state->country_id === (int) $country->id, 404);
        $state->update($this->stateValidated($request));

        return response()->json(['data' => $state->fresh()]);
    }

    public function destroyState(Country $country, State $state): JsonResponse
    {
        $this->authorize('update', $country);
        abort_unless((int) $state->country_id === (int) $country->id, 404);
        $state->update(['deleted' => true]);

        return response()->json(['data' => $state->fresh()]);
    }

    /**
     * @return array<string, mixed>
     */
    private function countryValidated(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', Rule::unique('countries', 'code')->ignore($ignoreId)],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ]);

        // Legacy defaults missing coordinates to 0.
        $validated['lat'] = $validated['lat'] ?? 0;
        $validated['lng'] = $validated['lng'] ?? 0;

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function stateValidated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ]);

        $validated['lat'] = $validated['lat'] ?? 0;
        $validated['lng'] = $validated['lng'] ?? 0;
        $validated['deleted'] = false;

        return $validated;
    }
}
