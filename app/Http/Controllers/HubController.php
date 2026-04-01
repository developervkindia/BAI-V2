<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\HubDashboardService;
use App\Services\PlanService;
use App\Services\ProductAccessService;
use Illuminate\Http\Request;

class HubController extends Controller
{
    public function __construct(
        protected ProductAccessService $productAccess,
        protected HubDashboardService $dashboardService,
        protected PlanService $planService,
    ) {}

    public function index(Request $request)
    {
        $user          = $request->user();
        $currentOrg    = $user->currentOrganization();
        $organizations = $user->allOrganizations();
        $allProducts   = Product::orderBy('sort_order')->get();
        $accessibleKeys = $this->productAccess
            ->getAccessibleProducts($user)
            ->pluck('key')
            ->toArray();

        $quickStats = [];
        $recentActivity = [];
        $planDetails = [];

        if ($currentOrg) {
            $quickStats = $this->dashboardService->getQuickStats($user, $currentOrg);
            $recentActivity = $this->dashboardService->getRecentActivity($user, $currentOrg, 8);

            foreach ($accessibleKeys as $key) {
                $planDetails[$key] = $this->planService->getPlanDetails($currentOrg, $key);
            }
        }

        return view('hub.index', [
            'currentOrg'     => $currentOrg,
            'organizations'  => $organizations,
            'allProducts'    => $allProducts,
            'accessibleKeys' => $accessibleKeys,
            'productConfig'  => config('products'),
            'quickStats'     => $quickStats,
            'recentActivity' => $recentActivity,
            'planDetails'    => $planDetails,
        ]);
    }
}
