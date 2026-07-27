<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('manageVendors', User::class);

        return response()->json([
            'data' => User::query()->where('role', UserRole::Cleaner)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manageVendors', User::class);

        $data = $this->validated($request);
        $data['role'] = UserRole::Cleaner;
        $user = User::query()->create($data);

        return response()->json(['data' => $user], 201);
    }

    public function update(Request $request, User $vendor): JsonResponse
    {
        $this->authorize('manageVendors', User::class);
        abort_unless($vendor->role === UserRole::Cleaner, 404);
        $vendor->update($this->validated($request));

        return response()->json(['data' => $vendor->fresh()]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->route('vendor'))],
            'password' => ['required_without:_method', 'nullable', 'string', 'min:8'],
            'active' => ['sometimes', 'boolean'],
        ]);
    }
}
