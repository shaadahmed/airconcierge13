<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerHasActiveAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->role !== UserRole::Owner) {
            return $next($request);
        }

        if ($user->hasActiveAccess()) {
            return $next($request);
        }

        if ($request->routeIs(
            'admin.owner-statements.*',
            'admin.terms.*',
            'logout',
        )) {
            return $next($request);
        }

        return redirect()
            ->route('admin.owner-statements.index')
            ->with('owner-access-restricted', true);
    }
}
