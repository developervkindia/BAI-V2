<?php

namespace App\Http\Controllers;

use App\Models\OrganizationSubscription;
use App\Models\Product;
use App\Services\PlanService;
use App\Services\ProductAccessService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        protected PlanService $planService,
        protected ProductAccessService $productAccess,
    ) {}

    /**
     * Public pricing page showing all product plans.
     */
    public function pricing()
    {
        $products = Product::where('is_available', true)
            ->orderBy('sort_order')
            ->get();

        $plans = [];
        foreach ($products as $product) {
            $plans[$product->key] = $this->planService->getPlansComparison($product->key);
        }

        return view('subscriptions.pricing', [
            'products' => $products,
            'plans' => $plans,
        ]);
    }

    /**
     * Org admin's subscription management page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $org = $user->currentOrganization();

        abort_unless($org && $org->isAdmin($user), 403);

        $subscriptions = $org->subscriptions()->with('product')->get();
        $allProducts = Product::where('is_available', true)->orderBy('sort_order')->get();

        $planDetails = [];
        foreach ($subscriptions as $sub) {
            if ($sub->product) {
                $planDetails[$sub->product->key] = $this->planService->getPlanDetails($org, $sub->product->key);
            }
        }

        return view('subscriptions.index', [
            'organization' => $org,
            'subscriptions' => $subscriptions,
            'allProducts' => $allProducts,
            'planDetails' => $planDetails,
            'plansConfig' => config('plans'),
        ]);
    }

    /**
     * Initiate a plan upgrade/downgrade.
     */
    public function changePlan(Request $request)
    {
        $user = $request->user();
        $org = $user->currentOrganization();

        abort_unless($org && $org->isAdmin($user), 403);

        $validated = $request->validate([
            'product_key' => 'required|string|exists:products,key',
            'plan' => 'required|in:free,pro,enterprise',
        ]);

        $product = Product::where('key', $validated['product_key'])->firstOrFail();

        $subscription = $org->subscriptions()
            ->where('product_id', $product->id)
            ->first();

        if (!$subscription) {
            $subscription = $org->subscriptions()->create([
                'product_id' => $product->id,
                'plan' => $validated['plan'],
                'status' => 'active',
                'starts_at' => now(),
            ]);
        } else {
            $oldPlan = $subscription->plan;
            $subscription->update([
                'plan' => $validated['plan'],
                'status' => 'active',
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'previous_plan' => $oldPlan,
                    'changed_at' => now()->toISOString(),
                    'changed_by' => $user->id,
                ]),
            ]);
        }

        // Clear product access cache
        $this->productAccess->clearCacheForOrg($org);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Plan updated to {$validated['plan']} for {$product->name}.",
                'subscription' => $subscription->fresh()->load('product'),
            ]);
        }

        return back()->with('success', "Plan updated to {$validated['plan']} for {$product->name}.");
    }

    /**
     * Start a trial for a product.
     */
    public function startTrial(Request $request)
    {
        $user = $request->user();
        $org = $user->currentOrganization();

        abort_unless($org && $org->isAdmin($user), 403);

        $validated = $request->validate([
            'product_key' => 'required|string|exists:products,key',
        ]);

        $product = Product::where('key', $validated['product_key'])->firstOrFail();

        $existing = $org->subscriptions()
            ->where('product_id', $product->id)
            ->first();

        if ($existing && $existing->plan !== 'free') {
            return back()->with('error', 'You already have a paid plan for this product.');
        }

        $trialDays = 14;

        if ($existing) {
            $existing->update([
                'plan' => 'pro',
                'status' => 'trialing',
                'trial_ends_at' => now()->addDays($trialDays),
                'metadata' => array_merge($existing->metadata ?? [], [
                    'trial_started_at' => now()->toISOString(),
                ]),
            ]);
        } else {
            $org->subscriptions()->create([
                'product_id' => $product->id,
                'plan' => 'pro',
                'status' => 'trialing',
                'starts_at' => now(),
                'trial_ends_at' => now()->addDays($trialDays),
            ]);
        }

        $this->productAccess->clearCacheForOrg($org);

        return back()->with('success', "14-day Pro trial started for {$product->name}.");
    }

    /**
     * Usage stats for the subscription management page.
     */
    public function usage(Request $request)
    {
        $user = $request->user();
        $org = $user->currentOrganization();

        abort_unless($org && $org->isAdmin($user), 403);

        $usage = [];

        // Board usage
        $boardCount = \App\Models\Board::whereHas('workspace', fn ($q) => $q->where('organization_id', $org->id))
            ->where('is_archived', false)->count();
        $boardLimit = $this->planService->getFeature($org, 'board', 'max_boards');
        $usage['board'] = [
            'boards' => ['current' => $boardCount, 'limit' => $boardLimit],
        ];

        // Projects usage
        $projectCount = \App\Models\Project::where('organization_id', $org->id)->count();
        $projectLimit = $this->planService->getFeature($org, 'projects', 'max_projects');
        $usage['projects'] = [
            'projects' => ['current' => $projectCount, 'limit' => $projectLimit],
        ];

        // HR usage
        $employeeCount = \App\Models\EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')->count();
        $employeeLimit = $this->planService->getFeature($org, 'hr', 'max_employees');
        $usage['hr'] = [
            'employees' => ['current' => $employeeCount, 'limit' => $employeeLimit],
        ];

        if ($request->expectsJson()) {
            return response()->json(['usage' => $usage]);
        }

        return view('subscriptions.usage', [
            'organization' => $org,
            'usage' => $usage,
        ]);
    }
}
