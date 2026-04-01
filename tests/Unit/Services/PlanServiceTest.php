<?php

namespace Tests\Unit\Services;

use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\Product;
use App\Models\User;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PlanService $planService;
    protected Organization $org;
    protected Product $boardProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->planService = new PlanService();

        $user = User::factory()->create();

        $this->org = Organization::create([
            'name' => 'Test Org',
            'owner_id' => $user->id,
        ]);

        $this->boardProduct = Product::create([
            'key' => 'board',
            'name' => 'BAI Board',
            'tagline' => 'Kanban',
            'color' => 'indigo',
            'route_prefix' => 'board',
            'is_available' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_get_plan_returns_free_when_no_subscription(): void
    {
        $this->assertEquals('free', $this->planService->getPlan($this->org, 'board'));
    }

    public function test_get_plan_returns_correct_plan(): void
    {
        OrganizationSubscription::create([
            'organization_id' => $this->org->id,
            'product_id' => $this->boardProduct->id,
            'plan' => 'pro',
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $this->assertEquals('pro', $this->planService->getPlan($this->org, 'board'));
    }

    public function test_can_use_returns_false_for_disabled_feature(): void
    {
        // Free plan has automations=false
        $this->assertFalse($this->planService->canUse($this->org, 'board', 'automations'));
    }

    public function test_can_use_returns_true_for_enabled_feature(): void
    {
        OrganizationSubscription::create([
            'organization_id' => $this->org->id,
            'product_id' => $this->boardProduct->id,
            'plan' => 'pro',
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $this->assertTrue($this->planService->canUse($this->org, 'board', 'automations'));
    }

    public function test_within_limit_respects_free_plan_limits(): void
    {
        // Free plan has max_boards=5
        $this->assertTrue($this->planService->withinLimit($this->org, 'board', 'max_boards', 3));
        $this->assertFalse($this->planService->withinLimit($this->org, 'board', 'max_boards', 5));
    }

    public function test_within_limit_allows_unlimited_on_enterprise(): void
    {
        OrganizationSubscription::create([
            'organization_id' => $this->org->id,
            'product_id' => $this->boardProduct->id,
            'plan' => 'enterprise',
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $this->assertTrue($this->planService->withinLimit($this->org, 'board', 'max_boards', 9999));
    }

    public function test_get_plan_details_returns_full_info(): void
    {
        OrganizationSubscription::create([
            'organization_id' => $this->org->id,
            'product_id' => $this->boardProduct->id,
            'plan' => 'pro',
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $details = $this->planService->getPlanDetails($this->org, 'board');

        $this->assertEquals('pro', $details['plan']);
        $this->assertArrayHasKey('features', $details);
        $this->assertArrayHasKey('all_plans', $details);
        $this->assertNotNull($details['subscription']);
    }
}
