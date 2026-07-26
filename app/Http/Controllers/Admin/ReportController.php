<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Reports\ReportIndexRequest;
use App\Http\Requests\Admin\Reports\ReportMetricsRequest;
use App\Http\Requests\Admin\Reports\ReportTotRequest;
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
}
