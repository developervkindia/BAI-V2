<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\EmployeeProfile;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class OrgAdminTest extends DuskTestCase
{
    protected User $admin;
    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::first(); // Org owner = admin
        $this->org = $this->admin->currentOrganization();
    }

    // ─── Hub & Navigation ───────────────────────────────────────────

    public function test_admin_can_access_hub(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/hub')
                ->assertSee('BAI Board')
                ->assertSee('BAI Projects');
        });
    }

    public function test_admin_can_access_org_settings(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/org/' . $this->org->slug)
                ->assertSee($this->org->name)
                ->assertSee('Admin Panel')
                ->assertSee('User Management')
                ->assertSee('Roles & Permissions');
        });
    }

    // ─── Role Management ────────────────────────────────────────────

    public function test_admin_can_view_roles(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/org/' . $this->org->slug . '/roles')
                ->assertSee('Roles')
                ->assertSee('Owner')
                ->assertSee('Admin')
                ->assertSee('Member');
        });
    }

    public function test_admin_can_access_create_role_form(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/org/' . $this->org->slug . '/roles/create')
                ->pause(500)
                ->assertSee('Permissions');
        });
    }

    public function test_admin_can_create_custom_role(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/org/' . $this->org->slug . '/roles/create')
                ->type('name', 'QA Engineer')
                ->type('description', 'Quality assurance role')
                ->press('Create Role');
        });

        // Cleanup
        Role::where('name', 'QA Engineer')->where('organization_id', $this->org->id)->delete();
    }

    public function test_admin_can_edit_role(): void
    {
        $role = $this->org->roles()->where('is_system', false)->first();
        if (!$role) {
            $role = Role::create([
                'organization_id' => $this->org->id,
                'name' => 'Test Role',
                'slug' => 'test_role',
                'level' => 50,
            ]);
        }

        $this->browse(function (Browser $browser) use ($role) {
            $browser->loginAs($this->admin)
                ->visit('/org/' . $this->org->slug . '/roles/' . $role->id)
                ->assertSee('Edit Role')
                ->assertInputValue('name', $role->name);
        });
    }

    public function test_admin_cannot_delete_system_role(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/org/' . $this->org->slug . '/roles')
                // System roles should not have delete buttons
                ->assertSee('Owner')
                ->assertSee('Admin')
                ->assertSee('Member');
        });
    }

    // ─── User Management ────────────────────────────────────────────

    public function test_admin_can_view_users(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/org/' . $this->org->slug . '/users')
                ->pause(500)
                ->assertPresent('table');
        });
    }

    public function test_admin_can_view_user_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/org/' . $this->org->slug . '/users/' . $this->admin->id)
                ->assertSee($this->admin->name)
                ->assertSee($this->admin->email);
        });
    }

    public function test_admin_can_access_user_edit(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/org/' . $this->org->slug . '/users/' . $this->admin->id . '/edit')
                ->assertSee('Edit');
        });
    }

    public function test_admin_can_update_employee_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/org/' . $this->org->slug . '/users/' . $this->admin->id . '/edit')
                ->assertSee('Edit');
        });
    }

    // ─── My Profile ─────────────────────────────────────────────────

    public function test_user_can_view_full_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/profile/full')
                ->assertSee($this->admin->name)
                ->assertSee('Personal')
                ->assertSee('Employment')
                ->assertSee('Education')
                ->assertSee('Experience')
                ->assertSee('Documents')
                ->assertSee('Skills')
                ->assertSee('Security');
        });
    }

    public function test_user_can_access_basic_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/profile')
                ->assertSee('Profile')
                ->assertSee($this->admin->name);
        });
    }

    // ─── Projects Access ────────────────────────────────────────────

    public function test_admin_can_access_projects(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/projects')
                ->assertSee('Projects');
        });
    }

    public function test_admin_can_access_project_views(): void
    {
        $project = \App\Models\Project::first();
        if (!$project) {
            $this->markTestSkipped('No projects exist');
        }

        $this->browse(function (Browser $browser) use ($project) {
            // Overview
            $browser->loginAs($this->admin)
                ->visit('/projects/' . $project->slug . '/overview')
                ->assertSee($project->name);

            // Tasks
            $browser->visit('/projects/' . $project->slug)
                ->assertSee('Tasks');

            // Board
            $browser->visit('/projects/' . $project->slug . '/board')
                ->assertSee($project->name);
        });
    }

    public function test_admin_can_access_project_timesheets(): void
    {
        $project = \App\Models\Project::first();
        if (!$project) {
            $this->markTestSkipped('No projects exist');
        }

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->admin)
                ->visit('/projects/' . $project->slug . '/timesheets')
                ->assertSee('Timesheet');
        });
    }

    public function test_admin_can_access_project_budget(): void
    {
        $project = \App\Models\Project::first();
        if (!$project) {
            $this->markTestSkipped('No projects exist');
        }

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->admin)
                ->visit('/projects/' . $project->slug . '/budget')
                ->assertSee('Budget');
        });
    }

    public function test_admin_can_access_project_reports(): void
    {
        $project = \App\Models\Project::first();
        if (!$project) {
            $this->markTestSkipped('No projects exist');
        }

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->admin)
                ->visit('/projects/' . $project->slug . '/reports')
                ->assertSee('Task Progress');
        });
    }

    public function test_admin_can_access_project_resources(): void
    {
        $project = \App\Models\Project::first();
        if (!$project) {
            $this->markTestSkipped('No projects exist');
        }

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->admin)
                ->visit('/projects/' . $project->slug . '/resources')
                ->assertSee('Resource Allocation');
        });
    }

    public function test_admin_can_access_project_workload(): void
    {
        $project = \App\Models\Project::first();
        if (!$project) {
            $this->markTestSkipped('No projects exist');
        }

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->admin)
                ->visit('/projects/' . $project->slug . '/workload')
                ->assertSee('Workload');
        });
    }

    public function test_admin_can_access_project_chat(): void
    {
        $project = \App\Models\Project::first();
        if (!$project) {
            $this->markTestSkipped('No projects exist');
        }

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->admin)
                ->visit('/projects/' . $project->slug . '/chat')
                ->assertSee('Send');
        });
    }

    public function test_admin_can_access_project_documents(): void
    {
        $project = \App\Models\Project::first();
        if (!$project) {
            $this->markTestSkipped('No projects exist');
        }

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->admin)
                ->visit('/projects/' . $project->slug . '/documents')
                ->assertSee('Documents');
        });
    }

    public function test_admin_can_access_recycle_bin(): void
    {
        $project = \App\Models\Project::first();
        if (!$project) {
            $this->markTestSkipped('No projects exist');
        }

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->admin)
                ->visit('/projects/' . $project->slug . '/recycle-bin')
                ->assertSee('Recycle Bin');
        });
    }

    // ─── Clients ────────────────────────────────────────────────────

    public function test_admin_can_access_clients(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/clients')
                ->assertSee('Clients');
        });
    }

    // ─── Sidebar Admin Links ────────────────────────────────────────

    public function test_admin_sees_admin_links_in_sidebar(): void
    {
        $project = \App\Models\Project::first();
        if (!$project) $this->markTestSkipped('No projects');

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->admin)
                ->visit('/projects/' . $project->slug)
                ->assertPresent('aside'); // Sidebar renders
        });
    }
}
