<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Geography\StoreCityRequest;
use App\Http\Requests\Admin\Geography\UpdateCityRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    public function store(StoreCityRequest $request, Country $country, State $state): JsonResponse
    {
        abort_unless($state->country_id === $country->id, 404);

        $city = $state->cities()->create($request->validated());

        return response()->json(['data' => $city], 201);
    }

    public function update(UpdateCityRequest $request, Country $country, State $state, City $city): JsonResponse
    {
        abort_unless($state->country_id === $country->id, 404);
        abort_unless($city->state_id === $state->id, 404);

        $city->update($request->validated());

        return response()->json(['data' => $city->fresh()]);
    }

    public function destroy(Country $country, State $state, City $city): JsonResponse
    {
        $this->authorize('update', $country);
        abort_unless($state->country_id === $country->id, 404);
        abort_unless($city->state_id === $state->id, 404);

        $city->delete();

        return response()->json(['data' => ['id' => $city->id, 'deleted' => true]]);
    }
}
