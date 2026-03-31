<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class ProductAccessService
{
    /**
     * Get all products accessible to a user through their organizations.
     */
    public function getAccessibleProducts(User $user): Collection
    {
        $orgs = $user->allOrganizations();

        return $orgs->flatMap(function (Organization $org) {
            return $org->subscriptions
                ->filter(fn ($sub) => $sub->isActive())
                ->map(fn ($sub) => $sub->product)
                ->filter();
        })->unique('id')->values();
    }

    /**
     * Check if a user can access a specific product through any of their orgs.
     */
    public function userCanAccessProduct(User $user, string $productKey): bool
    {
        return $user->allOrganizations()->contains(
            fn (Organization $org) => $org->hasProduct($productKey)
        );
    }

    /**
     * Auto-provision free SmartBoard and SmartProjects access when an org is created.
     */
    public function provisionFreeSmartBoard(Organization $org): void
    {
        $freeKeys = ['board', 'projects', 'opportunity'];

        foreach ($freeKeys as $key) {
            $product = Product::where('key', $key)->first();
            if (!$product) {
                continue;
            }
            $org->subscriptions()->firstOrCreate(
                ['product_id' => $product->id],
                ['plan' => 'free', 'status' => 'active', 'starts_at' => now()]
            );
        }
    }
}
