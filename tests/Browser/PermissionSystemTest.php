<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PermissionSystemTest extends DuskTestCase
{
    protected User $owner;
    protected Organization $org;
    protected PermissionService $permService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::first();
        $this->org = $this->owner->currentOrganization();
        $this->permService = app(PermissionService::class);
    }

    // ─── Permission Service Unit Tests ──────────────────────────────

    public function test_owner_has_all_permissions(): void
    {
        $allPerms = Permission::pluck('key')->toArray();
        $userPerms = $this->permService->userPermissions($this->owner, $this->org);

        $this->assertCount(count($allPerms), $userPerms);
    }

    public function test_owner_can_access_every_permission(): void
    {
        $this->assertTrue($this->permService->userCan($this->owner, 'admin.roles.manage', $this->org));
        $this->assertTrue($this->permService->userCan($this->owner, 'projects.create', $this->org));
        $this->assertTrue($this->permService->userCan($this->owner, 'board.cards.create', $this->org));
        $this->assertTrue($this->permService->userCan($this->owner, 'financial.billing.manage', $this->org));
    }

    public function test_per_product_permission_check(): void
    {
        $this->assertTrue($this->permService->userCanForProduct($this->owner, 'board.cards.create', 'board', $this->org));
        $this->assertTrue($this->permService->userCanForProduct($this->owner, 'projects.view', 'projects', $this->org));
    }

    public function test_permissions_grouped_by_product(): void
    {
        $grouped = $this->permService->allPermissionsGroupedByProduct();

        $this->assertArrayHasKey('global', $grouped->toArray());
        $this->assertArrayHasKey('board', $grouped->toArray());
        $this->assertArrayHasKey('projects', $grouped->toArray());
    }

    public function test_global_permissions_have_no_product(): void
    {
        $globalPerms = Permission::whereNull('product_id')->get();
        $this->assertGreaterThan(0, $globalPerms->count());

        foreach ($globalPerms as $perm) {
            $this->assertNull($perm->product_id);
            $this->assertTrue(
                str_starts_with($perm->key, 'org.') || str_starts_with($perm->key, 'admin.'),
                "Global perm {$perm->key} should start with org. or admin."
            );
        }
    }

    public function test_board_permissions_belong_to_board_product(): void
    {
        $boardPerms = Permission::whereHas('product', fn($q) => $q->where('key', 'board'))->get();
        $this->assertGreaterThan(0, $boardPerms->count());

        foreach ($boardPerms as $perm) {
            $this->assertStringStartsWith('board.', $perm->key);
        }
    }

    public function test_project_permissions_belong_to_projects_product(): void
    {
        $projPerms = Permission::whereHas('product', fn($q) => $q->where('key', 'projects'))->get();
        $this->assertGreaterThan(0, $projPerms->count());
    }

    public function test_member_role_has_limited_permissions(): void
    {
        $memberRole = $this->org->roles()->where('slug', 'member')->first();
        $this->assertNotNull($memberRole);

        $permCount = $memberRole->permissions()->count();
        $totalCount = Permission::count();

        $this->assertLessThan($totalCount, $permCount);
        $this->assertGreaterThan(0, $permCount);
    }

    public function test_system_roles_exist(): void
    {
        $systemRoles = $this->org->roles()->where('is_system', true)->get();

        $this->assertGreaterThanOrEqual(3, $systemRoles->count());
        $slugs = $systemRoles->pluck('slug')->toArray();
        $this->assertContains('owner', $slugs);
        $this->assertContains('admin', $slugs);
        $this->assertContains('member', $slugs);
    }

    public function test_permission_counts(): void
    {
        $total = Permission::count();
        $global = Permission::whereNull('product_id')->count();
        $board = Permission::whereHas('product', fn($q) => $q->where('key', 'board'))->count();
        $projects = Permission::whereHas('product', fn($q) => $q->where('key', 'projects'))->count();

        $this->assertEquals(48, $total);
        $this->assertEquals(11, $global);
        $this->assertEquals(12, $board);
        $this->assertEquals(25, $projects);
    }

    // ─── Role Form Shows Product Tabs ───────────────────────────────

    public function test_role_form_shows_product_tabs(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->owner)
                ->visit('/org/' . $this->org->slug . '/roles/create')
                ->assertSee('Global')
                ->assertSee('BAI Board')
                ->assertSee('BAI Projects');
        });
    }

    // ─── Custom Role CRUD ───────────────────────────────────────────

    public function test_can_create_and_verify_custom_role(): void
    {
        $roleName = 'Test Developer ' . uniqid();

        $this->browse(function (Browser $browser) use ($roleName) {
            $browser->loginAs($this->owner)
                ->visit('/org/' . $this->org->slug . '/roles/create')
                ->type('name', $roleName)
                ->type('description', 'Test developer role')
                ->press('Create Role')
                ->assertSee($roleName);
        });

        // Verify in DB
        $role = Role::where('name', $roleName)->where('organization_id', $this->org->id)->first();
        $this->assertNotNull($role);
        $this->assertFalse($role->is_system);

        // Cleanup
        $role->delete();
    }

    // ─── Super Admin Bypasses All Permissions ───────────────────────

    public function test_super_admin_bypasses_permissions(): void
    {
        $superAdmin = User::where('is_super_admin', true)->first();
        if (!$superAdmin) $this->markTestSkipped('No super admin');

        // Super admin should have access to everything
        $this->assertTrue($this->permService->userCan($superAdmin, 'admin.roles.manage'));
        $this->assertTrue($this->permService->userCan($superAdmin, 'financial.billing.manage'));
        $this->assertTrue($this->permService->userCan($superAdmin, 'board.boards.delete'));
    }
}
