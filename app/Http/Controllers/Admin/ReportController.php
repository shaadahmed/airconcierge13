<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['year', 'month', 'region_id', 'property_id']);

        return response()->json([
            'data' => [
                'summary' => $this->reportService->bookingSummary($filters),
                'regions' => $this->reportService->regions(),
            ],
        ]);
    }

    public function tot(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->reportService->totReport($request->only(['year'])),
        ]);
    }

    public function metrics(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->reportService->propertyMetrics($request->only(['year', 'region_id'])),
        ]);
    }
}
