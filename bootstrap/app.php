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
        $middleware->alias([
            'company.member' => \App\Http\Middleware\EnsureCompanyMember::class,
            'not.client' => \App\Http\Middleware\RestrictClientToPortal::class,
            'client.role' => \App\Http\Middleware\EnsureClientRole::class,
            'module.access' => \App\Http\Middleware\EnsureModuleAccess::class,
            'super.admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
