<?php

namespace App\Http\Middleware;

use App\Services\ProductAccessService;
use Closure;
use Illuminate\Http\Request;

class EnsureProductAccess
{
    public function __construct(
        protected ProductAccessService $productAccess,
    ) {}

    public function handle(Request $request, Closure $next, string $productKey)
    {
        $user = $request->user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        if ($user->is_super_admin) {
            return $next($request);
        }

        if (!$this->productAccess->userCanAccessProduct($user, $productKey)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Your organization does not have access to this product.',
                    'product' => $productKey,
                ], 403);
            }

            return redirect()->route('hub')->with(
                'error',
                'Your organization does not have an active subscription for this product.'
            );
        }

        return $next($request);
    }
}
