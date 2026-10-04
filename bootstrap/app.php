<?php

use App\Http\Middleware\AcquireSystemWriteLock;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\NormalizeGermanNumbers;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        /*
         * Muss den kompletten Request inklusive Session-Persistierung
         * umschließen, damit Restore zuverlässig auf bereits laufende
         * Datenbank-Schreiber warten kann.
         */
        $middleware->web(prepend: [
            AcquireSystemWriteLock::class,
        ]);

        $middleware->api(prepend: [
            AcquireSystemWriteLock::class,
        ]);

        $middleware->web(append: [
            EnsureActiveUser::class,
            NormalizeGermanNumbers::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
