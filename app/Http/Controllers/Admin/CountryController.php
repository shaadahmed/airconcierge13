<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Country::class);

        return response()->json(['data' => Country::query()->with(['states' => fn ($query) => $query->notDeleted()->orderBy('name')])->orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Country::class);
        $country = Country::query()->create($this->countryValidated($request));

        return response()->json(['data' => $country], 201);
    }

    public function update(Request $request, Country $country): JsonResponse
    {
        $this->authorize('update', $country);
        $country->update($this->countryValidated($request));

        return response()->json(['data' => $country->fresh()]);
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
        abort_unless($state->country_id === $country->id, 404);
        $state->update($this->stateValidated($request));

        return response()->json(['data' => $state->fresh()]);
    }

    /** @return array<string, mixed> */
    private function countryValidated(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);
    }

    /** @return array<string, mixed> */
    private function stateValidated(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'deleted' => ['sometimes', 'boolean'],
        ]);
    }
}
