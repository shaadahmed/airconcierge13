<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutboundEmailLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class OutboundEmailLogController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        return response()->json(['data' => OutboundEmailLog::query()->latest()->limit(100)->get()]);
    }
}
