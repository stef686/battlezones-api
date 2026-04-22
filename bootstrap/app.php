<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('api')
                ->middleware('api')
                ->group([
                    __DIR__.'/../routes/api/auth.php',
                    __DIR__.'/../routes/api/users.php',
                    __DIR__.'/../routes/api/gallery.php',
                    __DIR__.'/../routes/api/conversations.php',
                    __DIR__.'/../routes/api/events.php',
                ]);
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request): bool {
            return $request->is('api/*');
        });
    })->create();
