<?php

use App\Http\Middleware\EnsureTenantOwnership;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'tenant.owner' => EnsureTenantOwnership::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => route('login'));

        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();

            if ($user?->isPengelola()) {
                return route('pengelola.dashboard');
            }

            if ($user?->isTenant()) {
                return route('tenant.dashboard');
            }

            return route('pembeli.index');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
