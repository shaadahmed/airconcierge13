<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Dashboard\DashboardRevenueChartRequest;
use App\Models\BookingPayment;
use App\Models\Owner;
use App\Models\OwnerBlock;
use App\Models\Property;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => $this->dashboardService->summaryStats(),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(['data' => $this->dashboardService->summaryStats()]);
    }

    public function revenueChart(DashboardRevenueChartRequest $request): JsonResponse
    {
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
        return response()->json([
            'data' => $this->dashboardService->ownerStatementSummary($owner),
        ]);
    }

    public function accounting(): JsonResponse
    {
        return response()->json(['data' => ['series' => $this->paymentSeries(), 'summary' => $this->dashboardService->summaryStats()]]);
    }

    public function ownerBlocks(): JsonResponse
    {
        return response()->json(['data' => OwnerBlock::query()->with(['property', 'owner'])->notDeleted()->latest()->limit(200)->get()]);
    }

    public function ownerCalendarView(): JsonResponse
    {
        return response()->json(['data' => OwnerBlock::query()->with('property')->notDeleted()->latest()->limit(200)->get()
            ->map(fn (OwnerBlock $block) => ['id' => $block->id, 'title' => $block->property?->property_title ?? 'Owner block', 'start' => $block->start_date?->toDateString(), 'end' => $block->end_date?->toDateString(), 'property_id' => $block->property_id, 'owner_id' => $block->owner_id])]);
    }

    public function thresholdProperties(): JsonResponse
    {
        return response()->json(['data' => Property::query()->notDeleted()->where('status', true)->orderBy('property_title')->get()]);
    }

    public function cashflow(): JsonResponse
    {
        return response()->json(['data' => $this->paymentSeries()]);
    }

    public function ownerStatements(): JsonResponse
    {
        return response()->json(['data' => ['route' => route('admin.owner-statements.index'), 'owners' => Owner::query()->notDeleted()->orderBy('full_name')->get(['id', 'full_name'])]]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        abort_unless($request->user() !== null, 401);
        $request->user()->update($request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255']]));

        return response()->json(['data' => $this->dashboardService->profile($request->user()->fresh())]);
    }

    /** @return \Illuminate\Support\Collection<int, object> */
    private function paymentSeries(): \Illuminate\Support\Collection
    {
        return BookingPayment::query()->notDeleted()->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, SUM(amount) as total')
            ->whereNotNull('payment_date')->groupBy('month')->orderBy('month')->get();
    }
}
