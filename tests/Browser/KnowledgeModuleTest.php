<?php

namespace Tests\Browser;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\Permission;
use App\Models\Product;
use App\Models\User;
use App\Services\OrgMemberOnboardingService;
use App\Services\PermissionService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\Gate;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class KnowledgeModuleTest extends DuskTestCase
{
    protected User $user;

    protected Organization $org;

    protected ?KnowledgeCategory $fixtureCategory = null;

    protected ?KnowledgeArticle $fixtureArticle = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (! User::query()->exists()) {
            $this->markTestSkipped('No users in database.');
        }

        $this->user = User::query()->firstOrFail();
        $resolvedOrg = $this->user->currentOrganization()
            ?? $this->user->allOrganizations()->first();

        if (! $resolvedOrg) {
            $this->bootstrapOrganizationForKnowledgeDusk();
        } else {
            $this->org = $resolvedOrg;
        }

        $this->ensureKnowledgeBaseSubscription();
        $this->seedKbFixtures();
    }

    /**
     * First user (e.g. super admin) may have no org; create one with owner role so KB routes resolve.
     */
    protected function bootstrapOrganizationForKnowledgeDusk(): void
    {
        if (! Permission::query()->where('key', 'knowledge.view')->exists()) {
            $this->seed(ProductSeeder::class);
            $this->seed(PermissionSeeder::class);
        }

        $this->org = Organization::query()->create([
            'name' => 'Dusk Knowledge '.substr(uniqid(), -8),
            'owner_id' => $this->user->id,
        ]);

        $this->org->members()->syncWithoutDetaching([
            $this->user->id => [
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        PermissionSeeder::seedRolesForOrg($this->org);
        app(OrgMemberOnboardingService::class)->provisionMember($this->org, $this->user, 'owner');
        $this->user = $this->user->fresh();
    }

    protected function tearDown(): void
    {
        if ($this->fixtureArticle) {
            $this->fixtureArticle->forceDelete();
            $this->fixtureArticle = null;
        }
        if ($this->fixtureCategory) {
            $this->fixtureCategory->delete();
            $this->fixtureCategory = null;
        }

        parent::tearDown();
    }

    protected function ensureKnowledgeBaseSubscription(): void
    {
        $kb = Product::query()->firstOrCreate(
            ['key' => 'knowledge_base'],
            [
                'name' => 'Knowledge Base',
                'tagline' => 'KB',
                'color' => 'sky',
                'route_prefix' => 'knowledge',
                'is_available' => true,
                'sort_order' => 8,
            ]
        );

        OrganizationSubscription::query()->firstOrCreate(
            [
                'organization_id' => $this->org->id,
                'product_id' => $kb->id,
            ],
            [
                'plan' => 'free',
                'status' => 'active',
                'starts_at' => now(),
            ]
        );
    }

    protected function seedKbFixtures(): void
    {
        $suffix = str_replace('.', '', uniqid('', true));
        $this->fixtureCategory = KnowledgeCategory::query()->create([
            'organization_id' => $this->org->id,
            'name' => 'Dusk KB '.$suffix,
            'slug' => 'dusk-kb-'.$suffix,
            'description' => 'Browser test fixture category.',
            'sort_order' => 990,
        ]);

        $this->fixtureArticle = KnowledgeArticle::query()->create([
            'organization_id' => $this->org->id,
            'knowledge_category_id' => $this->fixtureCategory->id,
            'author_id' => $this->user->id,
            'title' => 'Dusk KB article '.$suffix,
            'slug' => 'dusk-kb-article-'.$suffix,
            'excerpt' => 'Fixture excerpt for Dusk.',
            'body_html' => '<p>Dusk fixture paragraph for search and display.</p>',
            'status' => 'published',
            'published_at' => now(),
            'pinned' => false,
        ]);
    }

    protected function canModerateKnowledge(): bool
    {
        if ($this->user->is_super_admin) {
            return true;
        }

        return app(PermissionService::class)->userCan($this->user, 'knowledge.moderate');
    }

    protected function canContributeKnowledge(): bool
    {
        if ($this->user->is_super_admin) {
            return true;
        }

        return Gate::forUser($this->user)->allows('create', KnowledgeArticle::class);
    }

    public function test_guest_visiting_knowledge_is_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/knowledge')
                ->assertPathBeginsWith('/login');
        });
    }

    public function test_knowledge_hub_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/knowledge')
                ->assertSee('Knowledge Hub');
        });
    }

    public function test_knowledge_category_show_via_slug(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(route('knowledge.categories.show', $this->fixtureCategory))
                ->assertSee($this->fixtureCategory->name)
                ->assertSee($this->fixtureArticle->title);
        });
    }

    public function test_knowledge_category_show_via_numeric_id(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/knowledge/categories/'.$this->fixtureCategory->id)
                ->assertSee($this->fixtureCategory->name);
        });
    }

    public function test_knowledge_article_show_via_slug(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(route('knowledge.articles.show', $this->fixtureArticle))
                ->assertSee($this->fixtureArticle->title)
                ->assertSee('Dusk fixture paragraph');
        });
    }

    public function test_knowledge_article_show_via_numeric_id(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/knowledge/articles/'.$this->fixtureArticle->id)
                ->assertSee($this->fixtureArticle->title);
        });
    }

    public function test_knowledge_search_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(route('knowledge.search'))
                ->assertSee('Search');
        });
    }

    public function test_knowledge_search_finds_fixture_article(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(route('knowledge.search', ['q' => $this->fixtureArticle->title]))
                ->assertSee($this->fixtureArticle->title);
        });
    }

    public function test_knowledge_article_create_page_loads_when_allowed(): void
    {
        if (! $this->canContributeKnowledge()) {
            $this->markTestSkipped('User cannot create knowledge articles.');
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(route('knowledge.articles.create'))
                ->assertSee('New article')
                ->assertPresent('select[name="knowledge_category_id"]');
        });
    }

    public function test_knowledge_categories_index_when_moderator(): void
    {
        if (! $this->canModerateKnowledge()) {
            $this->markTestSkipped('User cannot moderate knowledge base.');
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(route('knowledge.categories.index'))
                ->assertSee('Categories')
                ->assertSee('New category');
        });
    }

    public function test_knowledge_category_create_form_when_moderator(): void
    {
        if (! $this->canModerateKnowledge()) {
            $this->markTestSkipped('User cannot moderate knowledge base.');
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(route('knowledge.categories.create'))
                ->assertSee('New category')
                ->assertPresent('input[name="name"]');
        });
    }

    public function test_knowledge_trash_page_when_moderator(): void
    {
        if (! $this->canModerateKnowledge()) {
            $this->markTestSkipped('User cannot moderate knowledge base.');
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(route('knowledge.trash'))
                ->assertSee('Deleted articles')
                ->assertSee('Trash is empty.');
        });
    }

    public function test_knowledge_article_edit_page_loads(): void
    {
        if (! Gate::forUser($this->user)->allows('update', $this->fixtureArticle)) {
            $this->markTestSkipped('User cannot edit the fixture article.');
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(route('knowledge.articles.edit', $this->fixtureArticle))
                ->assertSee('Content')
                ->assertInputValue('title', $this->fixtureArticle->title);
        });
    }

    public function test_knowledge_article_revisions_page_loads(): void
    {
        if (! Gate::forUser($this->user)->allows('update', $this->fixtureArticle)) {
            $this->markTestSkipped('User cannot access revisions for the fixture article.');
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(route('knowledge.articles.revisions.index', $this->fixtureArticle))
                ->assertSee('Revision history')
                ->assertSee($this->fixtureArticle->title);
        });
    }
}
