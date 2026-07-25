<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Owners\OwnerTermsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TermsController extends Controller
{
    public function __construct(private OwnerTermsService $ownerTermsService) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => $this->ownerTermsService->show($user),
        ]);
    }

    public function agree(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => $this->ownerTermsService->agree($user->fresh() ?? $user),
        ]);
    }

    public function disagree(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $this->ownerTermsService->disagree($user->fresh() ?? $user);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'data' => $result,
        ]);
    }
}
