<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Login UI is served by the Nuxt SPA (frontend/). Laravel only handles POST /login.
     */
    public function create(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Use the SPA login page.'], 404);
        }

        return redirect()->away(rtrim((string) config('app.frontend_url'), '/').'/login');
    }

    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => [
                    'id' => $request->user()?->id,
                    'name' => $request->user()?->name,
                    'email' => $request->user()?->email,
                    'role' => $request->user()?->role,
                ],
            ]);
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json(status: 204);
        }

        return redirect()->away(rtrim((string) config('app.frontend_url'), '/').'/login');
    }
}
