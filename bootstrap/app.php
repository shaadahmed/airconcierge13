<?php

use App\Http\Middleware\EnsureOwnerHasActiveAccess;
use App\Http\Middleware\EnsureOwnerTermsAgreed;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'owner.terms' => EnsureOwnerTermsAgreed::class,
            'owner.active' => EnsureOwnerHasActiveAccess::class,
        ]);

        $middleware->statefulApi();

        $middleware->validateCsrfTokens(except: [
            'wh/hostaway/booking/created',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->expectsJson()
                || $request->is('api/*')
                || $request->is('wh/*'),
        );
    })->create();
