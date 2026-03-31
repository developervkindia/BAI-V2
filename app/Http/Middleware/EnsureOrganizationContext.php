<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use App\Services\ProductAccessService;
use Closure;
use Illuminate\Http\Request;

class EnsureOrganizationContext
{
    public function __construct(
        protected ProductAccessService $productAccess,
        protected PermissionService $permissionService,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        // Super admin on super-admin routes — skip org requirement
        if ($user->is_super_admin && $request->routeIs('super-admin.*')) {
            view()->share('isSuperAdmin', true);
            return $next($request);
        }

        $currentOrg = $user->currentOrganization();

        if (!$currentOrg) {
            if (!$request->routeIs('organizations.create', 'organizations.store')) {
                return redirect()->route('organizations.create');
            }
            return $next($request);
        }

        // Block deactivated orgs (unless super admin)
        if (isset($currentOrg->is_active) && !$currentOrg->is_active && !$user->is_super_admin) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Organization has been deactivated.'], 403);
            }
            if (!$request->routeIs('organizations.*', 'hub')) {
                return redirect()->route('hub')->with('error', 'Your organization has been deactivated. Contact the administrator.');
            }
        }

        $products = $this->productAccess->getAccessibleProducts($user);
        $userPermissions = $this->permissionService->userPermissions($user, $currentOrg);

        view()->share('currentOrganization', $currentOrg);
        view()->share('accessibleProducts', $products);
        view()->share('userPermissions', $userPermissions);
        view()->share('isSuperAdmin', (bool) $user->is_super_admin);

        return $next($request);
    }
}
