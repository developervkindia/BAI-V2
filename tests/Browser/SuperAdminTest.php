<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SuperAdminTest extends DuskTestCase
{
    protected User $superAdmin;
    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::where('is_super_admin', true)->firstOrFail();
        $this->org = Organization::first();
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin')
                ->assertSee('Platform Admin')
                ->assertSee('Organizations')
                ->assertSee('Users')
                ->assertSee('Subscriptions');
        });
    }

    public function test_non_super_admin_cannot_access_dashboard(): void
    {
        $regularUser = User::where('is_super_admin', false)->first();
        if (!$regularUser) $this->markTestSkipped('No non-super-admin user');

        $this->browse(function (Browser $browser) use ($regularUser) {
            $browser->loginAs($regularUser)
                ->visit('/super-admin')
                ->assertSee('403');
        });
    }

    public function test_can_view_organizations_list(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/organizations')
                ->assertSee($this->org->name);
        });
    }

    public function test_can_view_organization_detail(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/organizations/' . $this->org->slug)
                ->assertSee($this->org->name)
                ->assertSee('Members');
        });
    }

    public function test_can_view_org_detail_with_actions(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/organizations/' . $this->org->slug)
                ->assertSee($this->org->name)
                ->assertSee('Members');
        });
    }

    public function test_can_view_users_list(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/users')
                ->assertSee($this->superAdmin->name);
        });
    }

    public function test_can_view_user_detail(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/users/' . $this->superAdmin->id)
                ->assertSee($this->superAdmin->name)
                ->assertSee($this->superAdmin->email);
        });
    }

    public function test_can_view_subscriptions(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/subscriptions')
                ->assertSee('Subscription');
        });
    }

    public function test_can_view_products(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/products')
                ->assertSee('BAI Board')
                ->assertSee('BAI Projects');
        });
    }

    public function test_can_view_audit_log(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin/audit-log')
                ->assertSee('Audit');
        });
    }

    public function test_super_admin_sees_platform_admin_link_in_hub(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                ->visit('/hub')
                ->assertSee('Platform Admin');
        });
    }

    public function test_super_admin_sidebar_has_all_navigation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                ->visit('/super-admin')
                ->assertSee('Dashboard')
                ->assertSee('Organizations')
                ->assertSee('Users')
                ->assertSee('Subscriptions')
                ->assertSee('Products')
                ->assertSee('Audit Log');
        });
    }
}
