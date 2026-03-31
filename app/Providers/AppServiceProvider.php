<?php

namespace App\Providers;

use App\Services\PermissionService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PermissionService::class);
    }

    public function boot(): void
    {
        // Register @can_permission Blade directive
        Blade::if('can_permission', function (string $key) {
            return auth()->check() && app(PermissionService::class)->userCan(auth()->user(), $key);
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
}
