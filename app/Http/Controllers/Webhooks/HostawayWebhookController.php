<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SyncHostawayReservationJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostawayWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // TODO Phase 2 — validate Hostaway webhook signature before trusting the payload.
        // Do not process business logic here until signature verification is implemented.

        SyncHostawayReservationJob::dispatch($request->all());

        return response()->json(null, 200);
    }
}
