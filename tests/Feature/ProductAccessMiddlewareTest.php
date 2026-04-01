<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAccessMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->org = Organization::create(['name' => 'Test Org', 'owner_id' => $this->user->id]);
        $this->org->members()->attach($this->user->id, ['role' => 'owner']);

        session(['current_org_id' => $this->org->id]);
    }

    public function test_user_redirected_when_no_product_access(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertRedirect(route('hub'));
    }

    public function test_user_can_access_product_with_subscription(): void
    {
        $product = Product::create([
            'key' => 'board', 'name' => 'Board', 'tagline' => 'T',
            'color' => 'indigo', 'route_prefix' => 'board',
            'is_available' => true, 'sort_order' => 1,
        ]);

        OrganizationSubscription::create([
            'organization_id' => $this->org->id,
            'product_id' => $product->id,
            'plan' => 'free',
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_super_admin_bypasses_product_access(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true]);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
    }
}
