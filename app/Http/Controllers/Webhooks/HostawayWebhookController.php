<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SyncHostawayReservationJob;
use App\Services\Hostaway\HostawayWebhookAuthenticator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostawayWebhookController extends Controller
{
    public function __construct(
        private HostawayWebhookAuthenticator $authenticator,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->authenticator->verify($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        SyncHostawayReservationJob::dispatch($request->all());

        return response()->json(null, 200);
    }
}
