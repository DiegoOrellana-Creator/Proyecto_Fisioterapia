<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render atiende HTTPS en su balanceador y reenvía la petición por HTTP.
        // Confiar en sus cabeceras X-Forwarded-* hace que las URLs y los assets salgan con https.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'rol' => \App\Http\Middleware\VerificarRol::class,
            'admin.general' => \App\Http\Middleware\SoloAdminGeneral::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
