<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $org;
    protected Product $boardProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->org = Organization::create(['name' => 'Test Org', 'owner_id' => $this->user->id]);
        $this->org->members()->attach($this->user->id, ['role' => 'owner']);

        $this->boardProduct = Product::create([
            'key' => 'board', 'name' => 'BAI Board', 'tagline' => 'T',
            'color' => 'indigo', 'route_prefix' => 'board',
            'is_available' => true, 'sort_order' => 1,
        ]);

        OrganizationSubscription::create([
            'organization_id' => $this->org->id,
            'product_id' => $this->boardProduct->id,
            'plan' => 'free',
            'status' => 'active',
            'starts_at' => now(),
        ]);

        session(['current_org_id' => $this->org->id]);
    }

    public function test_pricing_page_is_accessible(): void
    {
        $response = $this->get('/pricing');
        $response->assertStatus(200);
    }

    public function test_subscription_index_requires_auth(): void
    {
        $response = $this->get('/subscriptions');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_subscriptions(): void
    {
        $response = $this->actingAs($this->user)->get('/subscriptions');
        $response->assertStatus(200);
    }

    public function test_admin_can_change_plan(): void
    {
        $response = $this->actingAs($this->user)->post('/subscriptions/change-plan', [
            'product_key' => 'board',
            'plan' => 'pro',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('organization_subscriptions', [
            'organization_id' => $this->org->id,
            'product_id' => $this->boardProduct->id,
            'plan' => 'pro',
        ]);
    }

    public function test_admin_can_start_trial(): void
    {
        $response = $this->actingAs($this->user)->post('/subscriptions/start-trial', [
            'product_key' => 'board',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('organization_subscriptions', [
            'organization_id' => $this->org->id,
            'product_id' => $this->boardProduct->id,
            'plan' => 'pro',
            'status' => 'trialing',
        ]);
    }
}
