<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Placeholder for owner terms agreement (domain logic later).
 */
class TermsController extends Controller
{
    public function show(): JsonResponse
    {
        $this->authorize('viewOwnerTerms', User::class);

        return response()->json([
            'data' => [
                'message' => 'Owner terms agreement placeholder.',
            ],
        ]);
    }
}
