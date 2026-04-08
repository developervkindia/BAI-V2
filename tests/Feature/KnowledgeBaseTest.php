<?php

namespace Tests\Feature;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->org = Organization::create(['name' => 'KB Org', 'owner_id' => $this->user->id]);
        $this->org->members()->attach($this->user->id, ['role' => 'owner']);
        session(['current_org_id' => $this->org->id]);

        $this->seed(PermissionSeeder::class);

        $kb = Product::firstOrCreate(
            ['key' => 'knowledge_base'],
            [
                'name' => 'Knowledge Base', 'tagline' => 'KB', 'color' => 'sky',
                'route_prefix' => 'knowledge', 'is_available' => true, 'sort_order' => 8,
            ]
        );

        OrganizationSubscription::create([
            'organization_id' => $this->org->id,
            'product_id' => $kb->id,
            'plan' => 'free',
            'status' => 'active',
            'starts_at' => now(),
        ]);
    }

    public function test_kb_home_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('knowledge.index'));
        $response->assertStatus(200);
    }

    public function test_category_and_article_flow(): void
    {
        $this->actingAs($this->user);

        $cat = KnowledgeCategory::create([
            'organization_id' => $this->org->id,
            'name' => 'Laravel',
            'slug' => 'laravel',
            'sort_order' => 0,
        ]);

        $article = KnowledgeArticle::create([
            'organization_id' => $this->org->id,
            'knowledge_category_id' => $cat->id,
            'author_id' => $this->user->id,
            'title' => 'Queues guide',
            'slug' => 'queues-guide',
            'body_html' => '<p>Run workers with <code>php artisan queue:work</code>.</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get(route('knowledge.categories.show', $cat))->assertStatus(200)->assertSee('Queues guide');
        $this->get(route('knowledge.articles.show', $article))->assertStatus(200)->assertSee('queue:work');

        $this->get('/knowledge/categories/'.$cat->id)->assertStatus(200)->assertSee('Laravel');
        $this->get('/knowledge/articles/'.$article->id)->assertStatus(200)->assertSee('queue:work');
    }

    public function test_search_returns_match(): void
    {
        $cat = KnowledgeCategory::create([
            'organization_id' => $this->org->id,
            'name' => 'HR',
            'slug' => 'hr',
            'sort_order' => 0,
        ]);

        KnowledgeArticle::create([
            'organization_id' => $this->org->id,
            'knowledge_category_id' => $cat->id,
            'author_id' => $this->user->id,
            'title' => 'Onboarding checklist',
            'slug' => 'onboarding',
            'body_html' => '<p>Welcome packet and laptop setup.</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('knowledge.search', ['q' => 'Onboarding']));
        $response->assertStatus(200)->assertSee('Onboarding checklist');
    }

    public function test_guest_redirected_from_kb(): void
    {
        $this->get(route('knowledge.index'))->assertRedirect(route('login'));
    }
}
