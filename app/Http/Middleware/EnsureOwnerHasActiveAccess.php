<?php

namespace App\Http\Middleware;

use App\Models\User;
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

        if (! $user instanceof User || ! $user->isOwner()) {
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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Owner active property access required.',
                'redirect_to' => '/admin/owner-statements',
            ], 409);
        }

        return redirect()
            ->route('admin.owner-statements.index')
            ->with('owner-access-restricted', true);
    }
}
