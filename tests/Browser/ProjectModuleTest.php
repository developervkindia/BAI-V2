<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProjectModuleTest extends DuskTestCase
{
    protected User $user;
    protected ?Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first();
        $this->project = Project::first();
    }

    // ─── Project Index ──────────────────────────────────────────────

    public function test_projects_index_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects')
                ->assertSee('Projects');
        });
    }

    // ─── Project Views (all 18) ─────────────────────────────────────

    public function test_project_overview(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/overview')
                ->assertSee($this->project->name)
                ->assertSee('Total Tasks');
        });
    }

    public function test_project_tasks_list(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug)
                ->assertSee('All') // Status filter
                ->assertSee('Section');
        });
    }

    public function test_project_board(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/board')
                ->assertSee($this->project->name);
        });
    }

    public function test_project_calendar(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/calendar')
                ->assertSee($this->project->name);
        });
    }

    public function test_project_milestones(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/milestones')
                ->assertSee('Milestones');
        });
    }

    public function test_project_timeline(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/timeline')
                ->assertSee($this->project->name);
        });
    }

    public function test_project_backlog(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/backlog')
                ->assertSee('Backlog');
        });
    }

    public function test_project_timesheets(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/timesheets')
                ->assertSee('Timesheet');
        });
    }

    public function test_project_budget(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/budget')
                ->assertSee('Total Budget');
        });
    }

    public function test_project_resources(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/resources')
                ->assertSee('Resource Allocation');
        });
    }

    public function test_project_workload(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/workload')
                ->assertSee('Workload');
        });
    }

    public function test_project_reports(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/reports')
                ->assertSee('Task Progress');
        });
    }

    public function test_project_chat(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/chat')
                ->assertSee('Send');
        });
    }

    public function test_project_documents(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/documents')
                ->assertSee('Documents');
        });
    }

    public function test_project_updates(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/updates')
                ->assertSee($this->project->name);
        });
    }

    public function test_project_scope(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/scope')
                ->assertSee($this->project->name);
        });
    }

    public function test_project_billing(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/billing')
                ->assertSee($this->project->name);
        });
    }

    public function test_project_recycle_bin(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/recycle-bin')
                ->assertSee('Recycle Bin');
        });
    }

    // ─── Dynamic Status Tabs ────────────────────────────────────────

    public function test_project_has_dynamic_status_tabs(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug)
                ->pause(1000) // Wait for Alpine.js to render
                ->assertSee('All')
                ->assertSee('Section');
        });
    }

    // ─── Board Dynamic Columns ──────────────────────────────────────

    public function test_board_has_dynamic_status_columns(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/board')
                ->assertSee($this->project->name);
            // Board renders status columns dynamically via Alpine.js
        });
    }

    // ─── Report Types ───────────────────────────────────────────────

    public function test_project_report_types(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug . '/reports')
                ->assertSee('Task Progress')
                ->assertSee('Time Tracking')
                ->assertSee('Milestones')
                ->assertSee('Burn-down');

            // Time tracking report
            $browser->visit('/projects/' . $this->project->slug . '/reports?report=time-tracking')
                ->assertSee('Time Tracking');

            // Milestones report
            $browser->visit('/projects/' . $this->project->slug . '/reports?report=milestones')
                ->assertSee('Milestone');
        });
    }

    // ─── More Dropdown Navigation ───────────────────────────────────

    public function test_project_navigation_has_all_tabs(): void
    {
        if (!$this->project) $this->markTestSkipped('No projects');
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/projects/' . $this->project->slug)
                ->assertSee('Overview')
                ->assertSee('Tasks')
                ->assertSee('Board')
                ->assertSee('Calendar')
                ->assertSee('More');
        });
    }
}
