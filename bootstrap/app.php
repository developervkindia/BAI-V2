<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware(['api', 'throttle:api'])
                ->prefix('api/v1')
                ->group(base_path('routes/api_v1.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'org.context'      => \App\Http\Middleware\EnsureOrganizationContext::class,
            'permission'       => \App\Http\Middleware\CheckPermission::class,
            'super-admin'      => \App\Http\Middleware\EnsureSuperAdmin::class,
            'product.access'   => \App\Http\Middleware\EnsureProductAccess::class,
            'plan.feature'     => \App\Http\Middleware\EnsurePlanFeature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
