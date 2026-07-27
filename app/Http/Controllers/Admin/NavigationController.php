<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Navigation\AdminNavigationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function __construct(private AdminNavigationService $navigationService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        return response()->json([
            'data' => $this->navigationService->forUser($user),
        ]);
    }
}
