<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Geography\StoreStateRequest;
use App\Http\Requests\Admin\Geography\UpdateStateRequest;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;

class StateController extends Controller
{
    public function store(StoreStateRequest $request, Country $country): JsonResponse
    {
        $state = $country->states()->create($request->validated());

        return response()->json(['data' => $state->load('cities')], 201);
    }

    public function update(UpdateStateRequest $request, Country $country, State $state): JsonResponse
    {
        abort_unless($state->country_id === $country->id, 404);

        $state->update($request->validated());

        return response()->json(['data' => $state->fresh('cities')]);
    }

    public function destroy(Country $country, State $state): JsonResponse
    {
        $this->authorize('update', $country);
        abort_unless($state->country_id === $country->id, 404);

        $state->cities()->each(fn ($city) => $city->delete());
        $state->delete();

        return response()->json(['data' => ['id' => $state->id, 'deleted' => true]]);
    }
}
