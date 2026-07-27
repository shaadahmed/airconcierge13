<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class SystemSettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        return response()->json(['data' => [
            'settings' => [
                'app.name' => config('app.name'), 'app.env' => config('app.env'),
                'app.url' => config('app.url'), 'mail.default' => config('mail.default'),
            ],
            'note' => 'Settings are read-only. Configure environment-specific values through deployment configuration.',
        ]]);
    }
}
