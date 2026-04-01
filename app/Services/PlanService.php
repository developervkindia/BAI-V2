<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationSubscription;

class PlanService
{
    /**
     * Get the active plan name for a product within an organization.
     * Returns 'free' if no subscription exists.
     */
    public function getPlan(Organization $org, string $productKey): string
    {
        $subscription = $org->subscriptions()
            ->whereHas('product', fn ($q) => $q->where('key', $productKey))
            ->whereIn('status', ['active', 'trialing'])
            ->first();

        if (!$subscription || !$subscription->isActive()) {
            return 'free';
        }

        return $subscription->plan ?? 'free';
    }

    /**
     * Get a specific feature value for an org's plan on a product.
     * Returns null if the product or feature is not defined.
     */
    public function getFeature(Organization $org, string $productKey, string $featureName): mixed
    {
        $plan = $this->getPlan($org, $productKey);
        return config("plans.{$productKey}.{$plan}.{$featureName}");
    }

    /**
     * Check if a boolean feature is enabled for the org's plan.
     */
    public function canUse(Organization $org, string $productKey, string $featureName): bool
    {
        return (bool) $this->getFeature($org, $productKey, $featureName);
    }

    /**
     * Check if a numeric limit has been reached.
     * Returns true if still within limit, false if exceeded.
     * Null limits mean unlimited.
     */
    public function withinLimit(Organization $org, string $productKey, string $featureName, int $currentCount): bool
    {
        $limit = $this->getFeature($org, $productKey, $featureName);

        if ($limit === null) {
            return true;
        }

        return $currentCount < (int) $limit;
    }

    /**
     * Get full plan details for UI display.
     */
    public function getPlanDetails(Organization $org, string $productKey): array
    {
        $plan = $this->getPlan($org, $productKey);
        $features = config("plans.{$productKey}.{$plan}", []);
        $subscription = $org->subscriptions()
            ->whereHas('product', fn ($q) => $q->where('key', $productKey))
            ->with('product')
            ->first();

        return [
            'plan' => $plan,
            'features' => $features,
            'subscription' => $subscription,
            'all_plans' => config("plans.{$productKey}", []),
        ];
    }

    /**
     * Get a comparison of all plans for a product (for pricing pages).
     */
    public function getPlansComparison(string $productKey): array
    {
        return config("plans.{$productKey}", []);
    }
}
