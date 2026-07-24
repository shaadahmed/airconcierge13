<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Placeholder for owner-statements routes allowed when active-access is restricted.
 */
class OwnerStatementController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewOwnerStatements', User::class);

        return response()->json([
            'data' => [
                'message' => 'Owner statements placeholder.',
            ],
        ]);
    }
}
