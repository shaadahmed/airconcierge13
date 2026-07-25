<?php

namespace App\Http\Middleware;

use App\Models\User;
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

        if (! $user instanceof User || ! $user->isOwner()) {
            return $next($request);
        }

        if ($user->hasAgreedToTerms()) {
            return $next($request);
        }

        if ($request->routeIs('admin.terms.*', 'logout')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Owner terms agreement required.',
                'redirect_to' => '/admin/terms',
            ], 409);
        }

        return redirect()->route('admin.terms.show');
    }
}
