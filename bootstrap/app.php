<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureOrganizationContext;
use App\Http\Middleware\EnsurePlanFeature;
use App\Http\Middleware\EnsureProductAccess;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\SyncRouteOrganization;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware(['api', 'throttle:api'])
                ->prefix('api/v1')
                ->group(base_path('routes/api_v1.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->routeIs('client-portal.*') || str_starts_with($request->path(), 'client-portal')) {
                return route('client-portal.login');
            }

            return route('login');
        });

        $middleware->alias([
            'org.context' => EnsureOrganizationContext::class,
            'permission' => CheckPermission::class,
            'super-admin' => EnsureSuperAdmin::class,
            'product.access' => EnsureProductAccess::class,
            'plan.feature' => EnsurePlanFeature::class,
            'org.route' => SyncRouteOrganization::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
