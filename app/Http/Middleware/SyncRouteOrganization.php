<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Services\PermissionService;
use App\Services\ProductAccessService;
use Closure;
use Illuminate\Http\Request;

/**
 * Aligns session current_org_id with the {organization} route parameter so
 * permission checks use the same org as the URL.
 */
class SyncRouteOrganization
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $org = $request->route('organization');
        if (! $org instanceof Organization) {
            return $next($request);
        }

        if ($user->is_super_admin) {
            session(['current_org_id' => $org->id]);
            view()->share('currentOrganization', $org);

            return $next($request);
        }

        abort_unless($org->hasUser($user), 403);

        $switched = $user->currentOrganization()?->id !== $org->id;
        if ($switched) {
            abort_unless(
                $user->allOrganizations()->contains(fn ($o) => $o->id === $org->id),
                403
            );
            session(['current_org_id' => $org->id]);
            $productAccess = app(ProductAccessService::class);
            $permissionService = app(PermissionService::class);
            view()->share('currentOrganization', $org);
            view()->share('accessibleProducts', $productAccess->getAccessibleProducts($user));
            view()->share('userPermissions', $permissionService->userPermissions($user, $org));
        }

        return $next($request);
    }
}
