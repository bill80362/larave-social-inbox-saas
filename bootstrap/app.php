<?php

use App\Http\Middleware\VerifyFacebookSignature;
use App\Http\Middleware\VerifyGoogleBusinessSignature;
use App\Http\Middleware\VerifyInstagramSignature;
use App\Http\Middleware\VerifyLineSignature;
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
            'webhook.verify.line' => VerifyLineSignature::class,
            'webhook.verify.facebook' => VerifyFacebookSignature::class,
            'webhook.verify.instagram' => VerifyInstagramSignature::class,
            'webhook.verify.google' => VerifyGoogleBusinessSignature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
