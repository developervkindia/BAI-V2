<?php

namespace App\Http\Middleware;

use App\Services\PlanService;
use Closure;
use Illuminate\Http\Request;

class EnsurePlanFeature
{
    public function __construct(
        protected PlanService $planService,
    ) {}

    /**
     * Usage: ->middleware('plan.feature:board,automations')
     * Checks that the current org's plan allows the feature.
     */
    public function handle(Request $request, Closure $next, string $productKey, string $featureName)
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        if ($user->is_super_admin) {
            return $next($request);
        }

        $org = $user->currentOrganization();

        if (! $org) {
            return $request->expectsJson()
                ? response()->json(['error' => 'No organization context.'], 403)
                : redirect()->route('hub');
        }

        if (! $this->planService->canUse($org, $productKey, $featureName)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'This feature requires a plan upgrade.',
                    'product' => $productKey,
                    'feature' => $featureName,
                    'current_plan' => $this->planService->getPlan($org, $productKey),
                ], 403);
            }

            return redirect()->route('subscriptions.index')->with(
                'error',
                'This feature requires a plan upgrade. You are currently on the '.ucfirst($this->planService->getPlan($org, $productKey)).' plan.'
            );
        }

        return $next($request);
    }
}
