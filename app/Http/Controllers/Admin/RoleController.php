<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        return response()->json(['data' => array_map(fn (UserRole $role) => ['value' => $role->value, 'name' => $role->name], UserRole::cases())]);
    }
}
