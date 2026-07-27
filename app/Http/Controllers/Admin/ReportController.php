<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Reports\ReportIndexRequest;
use App\Http\Requests\Admin\Reports\ReportMetricsRequest;
use App\Http\Requests\Admin\Reports\ReportTotRequest;
use App\Models\Guest;
use App\Models\OwnerBlock;
use App\Models\Property;
use App\Models\PropertyMonthlyMetric;
use App\Services\Reports\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(ReportIndexRequest $request): JsonResponse
    {
        $filters = $request->safe()->only(['year', 'month', 'region_id', 'property_id']);

        return response()->json([
            'data' => [
                'summary' => $this->reportService->bookingSummary($filters),
                'regions' => $this->reportService->regions(),
            ],
        ]);
    }

    public function tot(ReportTotRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->reportService->totReport($request->safe()->only(['year'])),
        ]);
    }

    public function metrics(ReportMetricsRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->reportService->propertyMetrics($request->safe()->only(['year', 'region_id'])),
        ]);
    }

    public function propertiesReport(ReportIndexRequest $request): JsonResponse { return $this->index($request); }
    public function nightsPayouts(): JsonResponse { return response()->json(['data' => $this->reportService->bookingSummary()]); }
    public function bookingStay(): JsonResponse { return response()->json(['data' => $this->reportService->bookingSummary()]); }
    public function regionsIncome(): JsonResponse { return response()->json(['data' => $this->reportService->totReport()]); }
    public function totalIncome(): JsonResponse { return response()->json(['data' => ['total' => (float) $this->reportService->bookingSummary()->sum('revenue')]]); }
    public function guestLocation(): JsonResponse
    {
        return response()->json(['data' => Guest::query()->selectRaw('country, state, COUNT(*) as guest_count')
            ->where(fn ($query) => $query->where('deleted', false)->orWhereNull('deleted'))->groupBy('country', 'state')->get()]);
    }
    public function threshold(): JsonResponse { return response()->json(['data' => Property::query()->notDeleted()->where('status', true)->orderBy('property_title')->get()]); }
    public function closingHistory(): JsonResponse { return response()->json(['data' => PropertyMonthlyMetric::query()->latest()->get()]); }
    public function ownerBlockAbandonment(): JsonResponse
    {
        return response()->json(['data' => OwnerBlock::query()->with(['property', 'owner'])->where(fn ($query) => $query->where('deleted', true)->orWhere('notes', 'like', '%abandon%'))->latest()->get()]);
    }
}
