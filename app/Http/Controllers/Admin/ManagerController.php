<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Manager::class, 'manager');
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => Manager::query()->notDeleted()->orderBy('last_name')->orderBy('first_name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Manager::class);
        $manager = Manager::query()->create($this->validated($request));

        return response()->json(['data' => $manager], 201);
    }

    public function update(Request $request, Manager $manager): JsonResponse
    {
        $this->authorize('update', $manager);
        $manager->update($this->validated($request));

        return response()->json(['data' => $manager->fresh()]);
    }

    public function destroy(Manager $manager): JsonResponse
    {
        $this->authorize('delete', $manager);
        $manager->update(['deleted' => true]);

        return response()->json(['data' => $manager->fresh()]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'], 'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:50'],
            'regional_manager' => ['sometimes', 'boolean'], 'is_active' => ['sometimes', 'boolean'],
            'compensation_structure' => ['nullable', 'string'], 'home_address' => ['nullable', 'string', 'max:100'],
            'employment_status' => ['nullable', 'integer'], 'salary_type' => ['nullable', 'integer'],
            'region' => ['nullable', 'in:ALL,SOME'], 'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:50'], 'zip' => ['nullable', 'string', 'max:10'],
        ]);
    }
}
