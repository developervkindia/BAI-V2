<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->user() || !$request->user()->is_super_admin) {
            abort(403, 'Super admin access required.');
        }

        return $next($request);
    }
}
