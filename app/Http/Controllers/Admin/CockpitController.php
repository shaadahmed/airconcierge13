<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CockpitController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        return response()->json(['data' => [
            'users' => User::query()->count(),
            'properties' => Property::query()->notDeleted()->count(),
            'bookings' => Booking::query()->notDeleted()->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
        ]]);
    }
}
