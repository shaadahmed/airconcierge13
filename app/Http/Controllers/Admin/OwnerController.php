<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Owner::class, 'owner');
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => Owner::query()->notDeleted()->with('region')->orderBy('full_name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Owner::class);
        $owner = Owner::query()->create($this->validated($request));

        return response()->json(['data' => $owner->load('region')], 201);
    }

    public function update(Request $request, Owner $owner): JsonResponse
    {
        $this->authorize('update', $owner);
        $owner->update($this->validated($request));

        return response()->json(['data' => $owner->fresh('region')]);
    }

    public function destroy(Owner $owner): JsonResponse
    {
        $this->authorize('delete', $owner);
        $owner->update(['deleted' => true]);

        return response()->json(['data' => $owner->fresh()]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'full_name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'owner_email' => ['nullable', 'email', 'max:100'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'owner_payout_information' => ['nullable', 'string'],
            'w9_on_file' => ['sometimes', 'boolean'],
            'emailstatus' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
