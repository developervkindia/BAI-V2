<?php

namespace Tests\Unit\Services;

use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAccessServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProductAccessService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductAccessService();
    }

    public function test_user_can_access_product_with_active_subscription(): void
    {
        $user = User::factory()->create();
        $org = Organization::create(['name' => 'Test Org', 'owner_id' => $user->id]);
        $org->members()->attach($user->id, ['role' => 'owner']);

        $product = Product::create([
            'key' => 'board', 'name' => 'Board', 'tagline' => 'T',
            'color' => 'indigo', 'route_prefix' => 'board',
            'is_available' => true, 'sort_order' => 1,
        ]);

        OrganizationSubscription::create([
            'organization_id' => $org->id,
            'product_id' => $product->id,
            'plan' => 'free',
            'status' => 'active',
            'starts_at' => now(),
        ]);

        session(['current_org_id' => $org->id]);

        $this->assertTrue($this->service->userCanAccessProduct($user, 'board'));
    }

    public function test_user_cannot_access_product_without_subscription(): void
    {
        $user = User::factory()->create();
        $org = Organization::create(['name' => 'Test Org', 'owner_id' => $user->id]);
        $org->members()->attach($user->id, ['role' => 'owner']);

        session(['current_org_id' => $org->id]);

        $this->assertFalse($this->service->userCanAccessProduct($user, 'board'));
    }

    public function test_provision_free_products_creates_subscriptions(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'owner_id' => 1]);

        foreach (['board', 'projects', 'opportunity', 'hr'] as $key) {
            Product::create([
                'key' => $key, 'name' => ucfirst($key), 'tagline' => 'T',
                'color' => 'indigo', 'route_prefix' => $key,
                'is_available' => true, 'sort_order' => 1,
            ]);
        }

        $this->service->provisionFreeSmartBoard($org);

        $this->assertEquals(4, $org->subscriptions()->count());
        $this->assertTrue($org->subscriptions()->whereHas('product', fn ($q) => $q->where('key', 'board'))->exists());
        $this->assertTrue($org->subscriptions()->whereHas('product', fn ($q) => $q->where('key', 'hr'))->exists());
    }
}
