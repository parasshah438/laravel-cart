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
        // Exclude external webhook routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'webhooks/shiprocket',
            'webhooks/razorpay',
            'webhook/razorpay',
            'webhook/stripe',
            'webhook/shiprocket',
            'webhook/shiprocket/pickup',
            'webhook/shiprocket/delivery',
            'webhook/shiprocket/return',
            'webhook/shiprocket/exception',
        ]);

        // Middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
