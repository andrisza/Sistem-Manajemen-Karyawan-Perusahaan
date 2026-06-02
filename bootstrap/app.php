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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role'             => \App\Http\Middleware\CheckRole::class,
            'set.session.role' => \App\Http\Middleware\SetSessionRole::class,
        ]);

        // Jalankan SetSessionRole di semua route web yang sudah auth
        $middleware->appendToGroup('web', \App\Http\Middleware\SetSessionRole::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
