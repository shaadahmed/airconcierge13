<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Geography\StoreCountryRequest;
use App\Http\Requests\Admin\Geography\UpdateCountryRequest;
use App\Models\Country;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Country::class, 'country');
    }

    public function index(): JsonResponse
    {
        $countries = Country::query()
            ->with(['states.cities'])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $countries]);
    }

    public function store(StoreCountryRequest $request): JsonResponse
    {
        $country = Country::query()->create($request->validated());

        return response()->json(['data' => $country->load(['states.cities'])], 201);
    }

    public function update(UpdateCountryRequest $request, Country $country): JsonResponse
    {
        $country->update($request->validated());

        return response()->json(['data' => $country->fresh(['states.cities'])]);
    }

    public function destroy(Country $country): JsonResponse
    {
        $country->states()->each(function ($state): void {
            $state->cities()->each(fn ($city) => $city->delete());
            $state->delete();
        });
        $country->delete();

        return response()->json(['data' => ['id' => $country->id, 'deleted' => true]]);
    }
}
