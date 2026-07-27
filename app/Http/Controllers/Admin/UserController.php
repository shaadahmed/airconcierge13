<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => User::query()->orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        return response()->json(['data' => User::query()->create($this->validated($request))], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        $user->update($this->validated($request, $user));

        return response()->json(['data' => $user->fresh()]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        abort_if($user->is($request->user()), 422, 'You cannot delete your own account.');
        $user->delete();

        return response()->json(['data' => ['id' => $user->id, 'deleted' => true]]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $existing = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($existing?->id)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'active' => ['sometimes', 'boolean'],
            'password' => [$existing ? 'nullable' : 'required', 'string', 'min:8'],
        ]);

        if ($existing && blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        return $validated;
    }
}
