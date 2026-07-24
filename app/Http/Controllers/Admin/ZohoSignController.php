<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chronology;
use App\Models\HelloSignDetail;
use App\Models\ZohoCodeDetail;
use App\Services\Zoho\ZohoSignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ZohoSignController extends Controller
{
    public function __construct(private ZohoSignService $zohoSignService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Chronology::class);

        $token = ZohoCodeDetail::query()
            ->where('is_deleted', 0)
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'data' => [
                'configured' => $token !== null,
                'expires_soon' => $token?->isExpired() ?? true,
                'updated_at' => $token?->update_dtm,
            ],
        ]);
    }

    public function download(HelloSignDetail $helloSignDetail): Response
    {
        $this->authorize('viewAny', Chronology::class);

        abort_unless(
            filled($helloSignDetail->zoho_request_id) && filled($helloSignDetail->zoho_document_id),
            404,
        );

        $pdf = $this->zohoSignService->downloadSignedPdf(
            (string) $helloSignDetail->zoho_request_id,
            (string) $helloSignDetail->zoho_document_id,
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="zoho-signed-'.$helloSignDetail->id.'.pdf"',
        ]);
    }
}
