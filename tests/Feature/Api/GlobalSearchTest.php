<?php

namespace Tests\Feature\Api;

use App\Models\Organization;
use App\Models\Product;
use App\Models\OrganizationSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
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

    public function test_search_requires_authentication(): void
    {
        $response = $this->getJson('/api/global-search?q=test');
        $response->assertStatus(401);
    }

    public function test_search_requires_query_parameter(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/global-search');
        $response->assertStatus(422);
    }

    public function test_search_returns_results(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/global-search?q=test');
        $response->assertStatus(200);
        $response->assertJsonStructure(['query', 'results', 'count']);
    }
}
