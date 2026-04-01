<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function __construct(protected PermissionService $permissionService) {}

    public function handle(Request $request, Closure $next, string ...$permissions): mixed
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        if ($user->is_super_admin) {
            return $next($request);
        }

        foreach ($permissions as $perm) {
            if (!$this->permissionService->userCan($user, $perm)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'You do not have permission to perform this action.'], 403);
                }
                abort(403, 'You do not have permission to perform this action.');
            }
        }

        return $next($request);
    }
}
