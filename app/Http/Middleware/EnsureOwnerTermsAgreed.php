<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerTermsAgreed
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasRole('Property Owner')) {
            return $next($request);
        }

        if ($user->hasAgreedToTerms()) {
            return $next($request);
        }

        if ($request->routeIs('admin.terms.*', 'logout')) {
            return $next($request);
        }

        return redirect()->route('admin.terms.show');
    }
}
