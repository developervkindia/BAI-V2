<?php

namespace App\Providers;

use App\Services\PermissionService;
use App\Services\PlanService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PermissionService::class);
        $this->app->singleton(PlanService::class);
    }

    public function boot(): void
    {
        $this->configureRateLimiting();

        // Register @can_permission Blade directive
        Blade::if('can_permission', function (string $key) {
            return auth()->check() && app(PermissionService::class)->userCan(auth()->user(), $key);
        });

        // Register @plan_feature Blade directive for plan-based UI gating
        Blade::if('plan_feature', function (string $productKey, string $featureName) {
            if (!auth()->check()) {
                return false;
            }
            $user = auth()->user();
            if ($user->is_super_admin) {
                return true;
            }
            $org = $user->currentOrganization();
            if (!$org) {
                return false;
            }
            return app(PlanService::class)->canUse($org, $productKey, $featureName);
        });

        View::composer('components.layouts.smartprojects', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $org  = $user->currentOrganization();
                if ($org) {
                    $sidebarProjects = \App\Models\Project::where('organization_id', $org->id)
                        ->where(function ($q) use ($user) {
                            $q->where('owner_id', $user->id)
                              ->orWhere('visibility', 'organization')
                              ->orWhereHas('members', fn ($q) => $q->where('user_id', $user->id));
                        })
                        ->select('id', 'name', 'slug', 'color', 'status')
                        ->orderBy('name')
                        ->limit(40)
                        ->get();
                    $view->with('sidebarProjects', $sidebarProjects);
                    return;
                }
            }
            $view->with('sidebarProjects', collect());
        });

        View::composer('components.layouts.hr', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $org  = $user->currentOrganization();
                if ($org) {
                    $hrDepartments = \App\Models\HrDepartment::where('organization_id', $org->id)
                        ->where('is_active', true)
                        ->withCount('employees')
                        ->orderBy('name')
                        ->get();
                    $view->with('hrSidebarDepartments', $hrDepartments);
                    return;
                }
            }
            $view->with('hrSidebarDepartments', collect());
        });

        View::composer('components.layouts.opportunity', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $org  = $user->currentOrganization();
                if ($org) {
                    $oppSidebarProjects = \App\Models\OppProject::where('organization_id', $org->id)
                        ->where('is_template', false)
                        ->where(function ($q) use ($user) {
                            $q->where('owner_id', $user->id)
                              ->orWhere('visibility', 'public')
                              ->orWhereHas('members', fn ($q) => $q->where('user_id', $user->id));
                        })
                        ->select('id', 'name', 'slug', 'color', 'status')
                        ->orderBy('name')
                        ->limit(40)
                        ->get();
                    $view->with('oppSidebarProjects', $oppSidebarProjects);
                    return;
                }
            }
            $view->with('oppSidebarProjects', collect());
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-write', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('global-search', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    }
}
