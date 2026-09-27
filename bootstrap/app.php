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
        $middleware->redirectGuestsTo(fn () => route('auth.login'));
        $middleware->redirectUsersTo(fn () => route('home'));
        $middleware->alias([
            'isAdmin' => \App\Http\Middleware\IsAdmin::class,
        ]);
        // Trust forwarded headers from the dev container / Codespaces proxy
        // so route() and asset() honor the original https host.
        $middleware->trustProxies(at: '*', headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
            | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
            | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
            | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO);
        // Stripe delivers webhooks without a CSRF token.
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
