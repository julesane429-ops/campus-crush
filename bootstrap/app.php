<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__ . '/../routes/channels.php',
        ['middleware' => ['web', 'auth']],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'profile' => \App\Http\Middleware\EnsureProfileExists::class,
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'banned' => \App\Http\Middleware\CheckBanned::class,
        ]);

        // Vérifier le ban sur toutes les requêtes web
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\CheckBanned::class,
            \App\Http\Middleware\UpdateLastSeen::class,
        ]);

        // Exclure le webhook PayDunya de la vérification CSRF
        $middleware->validateCsrfTokens(except: [
            'webhook/paydunya',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
