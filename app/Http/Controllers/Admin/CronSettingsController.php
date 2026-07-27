<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class CronSettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        return response()->json(['data' => [
            ['command' => 'schedule:run', 'frequency' => 'every minute', 'description' => 'Laravel scheduler entry point'],
            ['command' => 'queue:work', 'frequency' => 'continuous worker', 'description' => 'Redis queue worker; not a cron command'],
        ]]);
    }
}
