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
        $vendor->update($this->validated($request, $vendor));

        return response()->json(['data' => $vendor->fresh()]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $existing = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($existing?->id)],
            'password' => [$existing ? 'nullable' : 'required', 'string', 'min:8'],
            'active' => ['sometimes', 'boolean'],
        ]);

        if ($existing && blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        return $validated;
    }
}
