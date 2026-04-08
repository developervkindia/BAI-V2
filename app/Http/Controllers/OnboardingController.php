<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\PlanService;
use App\Services\ProductAccessService;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function __construct(
        protected PlanService $planService,
        protected ProductAccessService $productAccess,
    ) {}

    public function plans(Request $request)
    {
        $user = $request->user();
        $org = $user->currentOrganization();

        if (! $org) {
            return redirect()->route('hub');
        }

        $products = Product::where('is_available', true)
            ->orderBy('sort_order')
            ->get();

        $plans = [];
        foreach ($products as $product) {
            $plans[$product->key] = $this->planService->getPlansComparison($product->key);
        }

        return view('onboarding.plans', [
            'products' => $products,
            'plans' => $plans,
            'promoCode' => session('onboarding_promo_code', ''),
            'organization' => $org,
        ]);
    }

    public function selectPlans(Request $request)
    {
        $user = $request->user();
        $org = $user->currentOrganization();

        if (! $org) {
            return redirect()->route('hub');
        }

        $validated = $request->validate([
            'plans' => 'required|array',
            'plans.*' => 'in:free,pro',
            'promo_code' => 'nullable|string|max:100',
        ]);

        $promoCode = $validated['promo_code'] ?? null;
        $savedPromo = session('onboarding_promo_code');
        $promoValid = $promoCode && $savedPromo && $promoCode === $savedPromo;

        $upgradedProducts = [];

        foreach ($validated['plans'] as $productKey => $plan) {
            $product = Product::where('key', $productKey)->first();
            if (! $product) {
                continue;
            }

            if ($plan === 'pro' && $promoValid) {
                $subscription = $org->subscriptions()
                    ->where('product_id', $product->id)
                    ->first();

                if ($subscription) {
                    $subscription->update([
                        'plan' => 'pro',
                        'status' => 'active',
                        'metadata' => array_merge($subscription->metadata ?? [], [
                            'promo_code' => $promoCode,
                            'upgraded_at' => now()->toISOString(),
                        ]),
                    ]);
                } else {
                    $org->subscriptions()->create([
                        'product_id' => $product->id,
                        'plan' => 'pro',
                        'status' => 'active',
                        'starts_at' => now(),
                        'metadata' => ['promo_code' => $promoCode],
                    ]);
                }

                $upgradedProducts[] = $product->name;
            }
        }

        $this->productAccess->clearCacheForOrg($org);
        session()->forget('onboarding_promo_code');

        if (count($upgradedProducts) > 0) {
            $names = implode(', ', $upgradedProducts);

            return redirect()->route('hub')->with('success', "Welcome! Pro plan activated for: {$names}.");
        }

        return redirect()->route('hub')->with('success', 'Welcome! Your account is set up on the Free plan. You can upgrade anytime.');
    }
}
