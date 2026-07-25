<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OwnerStatements\OwnerStatementReportRequest;
use App\Models\User;
use App\Services\Owners\OwnerStatementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OwnerStatementController extends Controller
{
    public function __construct(private OwnerStatementService $ownerStatementService) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => [
                'properties' => $this->ownerStatementService->propertiesFor($user),
                'date_ranges' => [
                    'this_month',
                    'last_month',
                    'next_month',
                    'this_year',
                    'last_year',
                    'last_12_months',
                    'specific_month',
                ],
            ],
        ]);
    }

    public function report(OwnerStatementReportRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => $this->ownerStatementService->report($user, $request->validated()),
        ]);
    }

    public function export(OwnerStatementReportRequest $request): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->ownerStatementService->exportCsv($user, $request->validated());
    }
}
