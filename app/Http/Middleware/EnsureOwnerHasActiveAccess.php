<?php

namespace App\Http\Middleware;

use App\Contracts\OwnerActiveAccessChecker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerHasActiveAccess
{
    public function __construct(
        private readonly OwnerActiveAccessChecker $activeAccessChecker,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasRole('Property Owner')) {
            return $next($request);
        }

        if ($this->activeAccessChecker->hasActiveAccess($user)) {
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
