<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Owner;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => $this->dashboardService->summaryStats(),
        ]);
    }

    public function stats(): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        return response()->json(['data' => $this->dashboardService->summaryStats()]);
    }

    public function revenueChart(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        return response()->json([
            'data' => $this->dashboardService->revenueChart((int) $request->integer('months', 6)),
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        abort_unless($request->user() !== null, 401);

        return response()->json([
            'data' => $this->dashboardService->profile($request->user()),
        ]);
    }

    public function ownerStatement(Owner $owner): JsonResponse
    {
        $this->authorize('viewAny', Booking::class);

        return response()->json([
            'data' => $this->dashboardService->ownerStatementSummary($owner),
        ]);
    }
}
