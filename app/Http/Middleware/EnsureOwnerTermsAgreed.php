<?php

namespace App\Http\Middleware;

use App\Contracts\OwnerTermsChecker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerTermsAgreed
{
    public function __construct(
        private readonly OwnerTermsChecker $termsChecker,
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

        if ($this->termsChecker->hasAgreed($user)) {
            return $next($request);
        }

        if ($request->routeIs('admin.terms.*', 'logout')) {
            return $next($request);
        }

        return redirect()->route('admin.terms.show');
    }
}
