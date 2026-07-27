<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HostawayReservationLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class HostawayLogController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        return response()->json(['data' => HostawayReservationLog::query()->latest()->limit(100)->get()]);
    }
}
