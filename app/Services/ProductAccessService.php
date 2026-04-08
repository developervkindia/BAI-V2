<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductAccessService
{
    private array $requestCache = [];

    /**
     * Get all products accessible to a user through their organizations.
     * Cached per-request in memory and optionally in the cache store.
     */
    public function getAccessibleProducts(User $user): Collection
    {
        $cacheKey = "user_products:{$user->id}";

        if (isset($this->requestCache[$cacheKey])) {
            return $this->requestCache[$cacheKey];
        }

        // Check cache for product keys (serialization-safe plain array)
        $cachedKeys = Cache::get($cacheKey);

        if (is_array($cachedKeys) && ! empty($cachedKeys)) {
            $products = Product::whereIn('key', $cachedKeys)->get();
            $this->requestCache[$cacheKey] = $products;

            return $products;
        }

        // Cache miss -- compute fresh
        $orgs = $user->allOrganizations();

        $products = $orgs->flatMap(function (Organization $org) {
            return $org->subscriptions
                ->filter(fn ($sub) => $sub->isActive())
                ->map(fn ($sub) => $sub->product)
                ->filter();
        })->unique('id')->values();

        // Store only plain array of keys (avoids Eloquent serialization issues)
        Cache::put($cacheKey, $products->pluck('key')->toArray(), 300);

        $this->requestCache[$cacheKey] = $products;

        return $products;
    }

    /**
     * Check if a user can access a specific product through any of their orgs.
     */
    public function userCanAccessProduct(User $user, string $productKey): bool
    {
        return $this->getAccessibleProducts($user)->contains('key', $productKey);
    }

    /**
     * Auto-provision free product subscriptions when an org is created.
     */
    public function provisionFreeSmartBoard(Organization $org): void
    {
        $freeKeys = ['board', 'projects', 'opportunity', 'hr', 'knowledge_base', 'docs'];

        foreach ($freeKeys as $key) {
            $product = Product::where('key', $key)->first();
            if (! $product) {
                continue;
            }
            $org->subscriptions()->firstOrCreate(
                ['product_id' => $product->id],
                ['plan' => 'free', 'status' => 'active', 'starts_at' => now()]
            );
        }
    }

    /**
     * Subscribe the org to every catalog product on the enterprise tier so plan-based
     * feature gates (see config/plans.php) allow full functionality.
     */
    public function provisionEnterpriseForOrg(Organization $org): void
    {
        foreach (Product::query()->orderBy('sort_order')->get() as $product) {
            $org->subscriptions()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'plan' => 'enterprise',
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => null,
                ]
            );
        }

        $this->clearCacheForOrg($org);
    }

    /**
     * Bust the cache when subscriptions change.
     */
    public function clearCacheForUser(User $user): void
    {
        Cache::forget("user_products:{$user->id}");
        unset($this->requestCache["user_products:{$user->id}"]);
    }

    /**
     * Bust cache for all members of an org (e.g. when subscription changes).
     */
    public function clearCacheForOrg(Organization $org): void
    {
        $org->loadMissing('members');

        $org->members->each(fn ($member) => $this->clearCacheForUser($member));

        if ($org->owner_id) {
            $owner = User::query()->find($org->owner_id);
            if ($owner) {
                $this->clearCacheForUser($owner);
            }
        }
    }
}
