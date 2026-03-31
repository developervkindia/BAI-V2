<?php

namespace Tests\Browser;

use App\Models\OppComment;
use App\Models\OppGoal;
use App\Models\OppPortfolio;
use App\Models\OppProject;
use App\Models\OppSection;
use App\Models\OppTask;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class OpportunityTest extends DuskTestCase
{
    protected User $user;
    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first();
        $this->org = $this->user->currentOrganization();
    }

    // ─── Hub & Navigation ───────────────────────────────────────────

    public function test_opportunity_appears_in_hub(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/hub')
                ->assertSee('Opportunity');
        });
    }

    public function test_opportunity_home_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/opportunity')
                ->assertSee('Home');
        });
    }

    public function test_my_tasks_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/my-tasks')
                ->assertSee('My tasks');
        });
    }

    public function test_inbox_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/inbox')
                ->assertSee('Inbox');
        });
    }

    public function test_sidebar_shows_navigation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/opportunity')
                ->assertSee('Home')
                ->assertSee('My tasks')
                ->assertSee('Inbox')
                ->assertSee('Reporting')
                ->assertSee('Portfolios')
                ->assertSee('Goals');
        });
    }

    // ─── Project CRUD ───────────────────────────────────────────────

    public function test_projects_index_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/projects')
                ->assertSee('Projects');
        });
    }

    public function test_can_create_project(): void
    {
        $name = 'Dusk Project ' . Str::random(5);

        // Create via model (tests model + relationships + slug generation)
        $project = OppProject::create([
            'organization_id' => $this->org->id,
            'owner_id'        => $this->user->id,
            'name'            => $name,
            'status'          => 'on_track',
        ]);
        OppSection::create(['project_id' => $project->id, 'name' => 'Untitled section', 'position' => 1000]);
        $project->members()->attach($this->user->id, ['role' => 'owner']);

        // Verify DB state
        $this->assertNotNull($project->id);
        $this->assertNotNull($project->slug);
        $this->assertEquals($this->org->id, $project->organization_id);
        $this->assertGreaterThan(0, $project->sections()->count());
        $this->assertTrue($project->members()->where('user_id', $this->user->id)->exists());

        // Verify page renders with project
        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/projects/' . $project->slug)
                ->assertSee($project->name);
        });
    }

    public function test_project_list_view_loads(): void
    {
        $project = OppProject::where('organization_id', $this->org->id)->first();
        if (!$project) $this->markTestSkipped('No projects');

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/projects/' . $project->slug)
                ->assertSee($project->name)
                ->assertSee('List')
                ->assertSee('Board')
                ->assertSee('Timeline')
                ->assertSee('Calendar');
        });
    }

    public function test_project_board_view_loads(): void
    {
        $project = OppProject::where('organization_id', $this->org->id)->first();
        if (!$project) $this->markTestSkipped('No projects');

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/projects/' . $project->slug . '/board')
                ->assertSee($project->name);
        });
    }

    public function test_project_timeline_view_loads(): void
    {
        $project = OppProject::where('organization_id', $this->org->id)->first();
        if (!$project) $this->markTestSkipped('No projects');

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/projects/' . $project->slug . '/timeline')
                ->assertSee($project->name);
        });
    }

    public function test_project_calendar_view_loads(): void
    {
        $project = OppProject::where('organization_id', $this->org->id)->first();
        if (!$project) $this->markTestSkipped('No projects');

        $this->browse(function (Browser $browser) use ($project) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/projects/' . $project->slug . '/calendar')
                ->assertSee($project->name);
        });
    }

    // ─── Task CRUD (API-level) ──────────────────────────────────────

    public function test_can_create_task_via_api(): void
    {
        $project = OppProject::where('organization_id', $this->org->id)->first();
        $section = $project->sections()->first();

        $task = OppTask::create([
            'project_id'  => $project->id,
            'section_id'  => $section->id,
            'title'       => 'Dusk API Task',
            'assignee_id' => $this->user->id,
            'created_by'  => $this->user->id,
            'status'      => 'incomplete',
            'position'    => 99000,
        ]);

        $this->assertNotNull($task->id);
        $this->assertEquals('incomplete', $task->status);
    }

    public function test_can_complete_task(): void
    {
        $task = OppTask::where('status', 'incomplete')->first();
        if (!$task) $this->markTestSkipped('No incomplete tasks');

        $task->update([
            'status'       => 'complete',
            'completed_at' => now(),
            'completed_by' => $this->user->id,
        ]);

        $this->assertEquals('complete', $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);

        // Revert
        $task->update(['status' => 'incomplete', 'completed_at' => null, 'completed_by' => null]);
    }

    public function test_can_create_subtask(): void
    {
        $parent = OppTask::whereNull('parent_task_id')->first();
        if (!$parent) $this->markTestSkipped('No parent tasks');

        $subtask = OppTask::create([
            'project_id'     => $parent->project_id,
            'parent_task_id' => $parent->id,
            'title'          => 'Dusk Subtask',
            'created_by'     => $this->user->id,
            'position'       => 1000,
        ]);

        $this->assertEquals($parent->id, $subtask->parent_task_id);
        $this->assertTrue($parent->subtasks()->where('id', $subtask->id)->exists());
    }

    public function test_can_add_comment(): void
    {
        $task = OppTask::first();
        if (!$task) $this->markTestSkipped('No tasks');

        $comment = OppComment::create([
            'task_id' => $task->id,
            'user_id' => $this->user->id,
            'body'    => 'Dusk test comment ' . now()->timestamp,
        ]);

        $this->assertNotNull($comment->id);
        $this->assertTrue($task->comments()->where('id', $comment->id)->exists());
    }

    public function test_can_toggle_task_like(): void
    {
        $task = OppTask::first();
        if (!$task) $this->markTestSkipped('No tasks');

        $initialCount = $task->likes_count;

        \App\Models\OppTaskLike::firstOrCreate([
            'task_id' => $task->id,
            'user_id' => $this->user->id,
        ]);
        $task->increment('likes_count');

        $this->assertEquals($initialCount + 1, $task->fresh()->likes_count);

        // Unlike
        \App\Models\OppTaskLike::where('task_id', $task->id)->where('user_id', $this->user->id)->delete();
        $task->decrement('likes_count');
    }

    public function test_can_add_follower(): void
    {
        $task = OppTask::first();
        if (!$task) $this->markTestSkipped('No tasks');

        $task->followers()->syncWithoutDetaching([$this->user->id]);
        $this->assertTrue($task->followers()->where('user_id', $this->user->id)->exists());

        // Cleanup
        $task->followers()->detach($this->user->id);
    }

    public function test_can_add_tag(): void
    {
        $tag = \App\Models\OppTag::firstOrCreate(
            ['organization_id' => $this->org->id, 'name' => 'DuskTag'],
            ['color' => '#EF4444']
        );

        $task = OppTask::first();
        $task->tags()->syncWithoutDetaching([$tag->id]);
        $this->assertTrue($task->tags()->where('opp_tags.id', $tag->id)->exists());

        // Cleanup
        $task->tags()->detach($tag->id);
    }

    public function test_can_create_dependency(): void
    {
        $tasks = OppTask::whereNull('parent_task_id')->take(2)->get();
        if ($tasks->count() < 2) $this->markTestSkipped('Need 2 tasks');

        \App\Models\OppTaskDependency::firstOrCreate([
            'task_id'            => $tasks[0]->id,
            'depends_on_task_id' => $tasks[1]->id,
        ], ['type' => 'blocking']);

        $this->assertTrue($tasks[0]->dependencies()->where('depends_on_task_id', $tasks[1]->id)->exists());

        // Cleanup
        \App\Models\OppTaskDependency::where('task_id', $tasks[0]->id)->where('depends_on_task_id', $tasks[1]->id)->delete();
    }

    // ─── My Tasks API ───────────────────────────────────────────────

    public function test_my_tasks_api_returns_assigned_tasks(): void
    {
        // Ensure at least one task assigned to user
        $task = OppTask::where('assignee_id', $this->user->id)->first();
        if (!$task) {
            $project = OppProject::where('organization_id', $this->org->id)->first();
            $task = OppTask::create([
                'project_id' => $project->id, 'title' => 'My API Task',
                'assignee_id' => $this->user->id, 'created_by' => $this->user->id, 'position' => 1000,
            ]);
        }

        $this->actingAs($this->user)
            ->getJson('/api/opp/my-tasks')
            ->assertOk()
            ->assertJsonStructure(['tasks']);
    }

    public function test_task_show_api_returns_full_data(): void
    {
        $task = OppTask::first();
        if (!$task) $this->markTestSkipped('No tasks');

        $this->actingAs($this->user)
            ->getJson('/api/opp/tasks/' . $task->id)
            ->assertOk()
            ->assertJsonStructure(['task' => ['id', 'title', 'status', 'project_id']]);
    }

    public function test_search_api_works(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/opp/search?query=test')
            ->assertOk();
    }

    // ─── Goals ──────────────────────────────────────────────────────

    public function test_goals_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/goals')
                ->assertSee('Goals');
        });
    }

    public function test_can_create_goal(): void
    {
        $goal = OppGoal::create([
            'organization_id' => $this->org->id,
            'owner_id'        => $this->user->id,
            'title'           => 'Dusk Goal: Increase Revenue',
            'goal_type'       => 'company',
            'metric_type'     => 'percentage',
            'target_value'    => 100,
            'current_value'   => 35,
            'status'          => 'on_track',
            'due_date'        => now()->addMonths(3),
        ]);

        $this->assertNotNull($goal->id);
        $this->assertEquals(35, $goal->progress);
    }

    public function test_can_create_sub_goal(): void
    {
        $parent = OppGoal::where('organization_id', $this->org->id)->first();
        if (!$parent) $this->markTestSkipped('No parent goals');

        $child = OppGoal::create([
            'organization_id' => $this->org->id,
            'parent_id'       => $parent->id,
            'owner_id'        => $this->user->id,
            'title'           => 'Sub-goal: Q1 Target',
            'goal_type'       => 'team',
            'metric_type'     => 'number',
            'target_value'    => 50,
            'current_value'   => 20,
        ]);

        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertTrue($parent->children()->where('id', $child->id)->exists());
    }

    public function test_goal_show_page_loads(): void
    {
        $goal = OppGoal::where('organization_id', $this->org->id)->first();
        if (!$goal) $this->markTestSkipped('No goals');

        $this->browse(function (Browser $browser) use ($goal) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/goals/' . $goal->id)
                ->assertSee($goal->title);
        });
    }

    // ─── Portfolios ─────────────────────────────────────────────────

    public function test_portfolios_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/portfolios')
                ->assertSee('Portfolios');
        });
    }

    public function test_can_create_portfolio(): void
    {
        $portfolio = OppPortfolio::create([
            'organization_id' => $this->org->id,
            'owner_id'        => $this->user->id,
            'name'            => 'Dusk Portfolio',
            'color'           => '#3B82F6',
        ]);

        $this->assertNotNull($portfolio->id);
    }

    public function test_can_add_project_to_portfolio(): void
    {
        $portfolio = OppPortfolio::where('organization_id', $this->org->id)->first();
        $project = OppProject::where('organization_id', $this->org->id)->first();
        if (!$portfolio || !$project) $this->markTestSkipped('Need portfolio + project');

        $portfolio->projects()->syncWithoutDetaching([$project->id]);
        $this->assertTrue($portfolio->projects()->where('opp_projects.id', $project->id)->exists());
    }

    public function test_portfolio_show_page_loads(): void
    {
        $portfolio = OppPortfolio::where('organization_id', $this->org->id)->first();
        if (!$portfolio) $this->markTestSkipped('No portfolios');

        $this->browse(function (Browser $browser) use ($portfolio) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/portfolios/' . $portfolio->id)
                ->assertSee($portfolio->name);
        });
    }

    // ─── Reporting ──────────────────────────────────────────────────

    public function test_reporting_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/reporting')
                ->assertSee('Reporting');
        });
    }

    public function test_report_apis_return_data(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/opp/reports/team-workload')
            ->assertOk()
            ->assertJsonStructure(['workload']);

        $this->actingAs($this->user)
            ->getJson('/api/opp/reports/project-progress')
            ->assertOk()
            ->assertJsonStructure(['projects']);
    }

    // ─── Templates ──────────────────────────────────────────────────

    public function test_templates_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/opportunity/templates')
                ->assertSee('Templates');
        });
    }

    public function test_can_save_project_as_template(): void
    {
        $project = OppProject::where('organization_id', $this->org->id)
            ->where('is_template', false)
            ->first();
        if (!$project) $this->markTestSkipped('No projects');

        // Clone as template
        $template = $project->replicate();
        $template->name = $project->name . ' (Template)';
        $template->slug = Str::slug($template->name) . '-' . Str::random(6);
        $template->is_template = true;
        $template->save();

        $this->assertTrue($template->is_template);
        $this->assertNotEquals($project->id, $template->id);

        // Cleanup
        $template->forceDelete();
    }

    // ─── Workflow & Automation ───────────────────────────────────────

    public function test_can_create_rule(): void
    {
        $project = OppProject::where('organization_id', $this->org->id)->first();
        if (!$project) $this->markTestSkipped('No projects');

        $rule = \App\Models\OppRule::create([
            'project_id'    => $project->id,
            'name'          => 'Auto-assign on creation',
            'is_active'     => true,
            'trigger_type'  => 'task_added',
            'trigger_config'=> ['section_id' => null],
            'action_type'   => 'assign',
            'action_config' => ['user_id' => $this->user->id],
            'created_by'    => $this->user->id,
        ]);

        $this->assertNotNull($rule->id);
        $this->assertTrue($rule->is_active);
    }

    public function test_can_create_approval(): void
    {
        $task = OppTask::first();
        if (!$task) $this->markTestSkipped('No tasks');

        $approval = \App\Models\OppApproval::create([
            'task_id'      => $task->id,
            'status'       => 'pending',
            'requested_by' => $this->user->id,
        ]);

        $this->assertEquals('pending', $approval->status);

        // Approve it
        $approval->update([
            'status'     => 'approved',
            'decided_by' => $this->user->id,
            'decided_at' => now(),
        ]);

        $this->assertEquals('approved', $approval->fresh()->status);
    }

    // ─── Forms ──────────────────────────────────────────────────────

    public function test_can_create_form(): void
    {
        $project = OppProject::where('organization_id', $this->org->id)->first();
        if (!$project) $this->markTestSkipped('No projects');

        $form = \App\Models\OppForm::create([
            'project_id' => $project->id,
            'name'       => 'Bug Report Form',
            'is_active'  => true,
            'is_public'  => true,
            'fields'     => [
                ['name' => 'title', 'type' => 'text', 'required' => true],
                ['name' => 'severity', 'type' => 'dropdown', 'options' => ['Low', 'Medium', 'High']],
                ['name' => 'description', 'type' => 'textarea'],
            ],
            'created_by' => $this->user->id,
        ]);

        $this->assertNotNull($form->id);
        $this->assertNotNull($form->slug);
        $this->assertCount(3, $form->fields);
    }

    public function test_can_submit_form(): void
    {
        $form = \App\Models\OppForm::where('is_public', true)->first();
        if (!$form) $this->markTestSkipped('No public forms');

        $submission = \App\Models\OppFormSubmission::create([
            'form_id'            => $form->id,
            'data'               => ['title' => 'Test Bug', 'severity' => 'High', 'description' => 'Something broke'],
            'submitted_by_name'  => 'External User',
            'submitted_by_email' => 'external@test.com',
            'created_at'         => now(),
        ]);

        $this->assertNotNull($submission->id);
    }

    // ─── Favorites ──────────────────────────────────────────────────

    public function test_can_toggle_favorite(): void
    {
        $project = OppProject::where('organization_id', $this->org->id)->first();
        if (!$project) $this->markTestSkipped('No projects');

        $fav = \App\Models\OppFavorite::create([
            'user_id'        => $this->user->id,
            'favorable_type' => OppProject::class,
            'favorable_id'   => $project->id,
            'created_at'     => now(),
        ]);

        $this->assertNotNull($fav->id);

        // Unfavorite
        $fav->delete();
        $this->assertFalse(
            \App\Models\OppFavorite::where('user_id', $this->user->id)
                ->where('favorable_type', OppProject::class)
                ->where('favorable_id', $project->id)
                ->exists()
        );
    }

    // ─── Saved Searches ─────────────────────────────────────────────

    public function test_can_save_search(): void
    {
        $search = \App\Models\OppSavedSearch::create([
            'organization_id' => $this->org->id,
            'user_id'         => $this->user->id,
            'name'            => 'My overdue tasks',
            'filters'         => ['status' => 'incomplete', 'overdue' => true],
        ]);

        $this->assertNotNull($search->id);
        $this->assertEquals(['status' => 'incomplete', 'overdue' => true], $search->filters);
    }

    // ─── Custom Fields ──────────────────────────────────────────────

    public function test_can_create_custom_field(): void
    {
        $field = \App\Models\OppCustomField::create([
            'organization_id' => $this->org->id,
            'name'            => 'Priority Level',
            'type'            => 'dropdown',
            'options'         => ['P0', 'P1', 'P2', 'P3'],
        ]);

        $this->assertNotNull($field->id);
        $this->assertCount(4, $field->options);
    }

    public function test_can_set_custom_field_value(): void
    {
        $field = \App\Models\OppCustomField::where('organization_id', $this->org->id)->first();
        $task = OppTask::first();
        if (!$field || !$task) $this->markTestSkipped('Need field + task');

        \App\Models\OppTaskCustomFieldValue::updateOrCreate(
            ['task_id' => $task->id, 'custom_field_id' => $field->id],
            ['value' => 'P1']
        );

        $this->assertEquals('P1', $task->customFieldValues()->where('custom_field_id', $field->id)->value('value'));
    }

    // ─── Activity Log ───────────────────────────────────────────────

    public function test_activity_log_records_actions(): void
    {
        $task = OppTask::first();
        if (!$task) $this->markTestSkipped('No tasks');

        \App\Models\OppActivityLog::create([
            'task_id'    => $task->id,
            'project_id' => $task->project_id,
            'user_id'    => $this->user->id,
            'action'     => 'task.created',
            'created_at' => now(),
        ]);

        $this->assertTrue(
            \App\Models\OppActivityLog::where('task_id', $task->id)->where('action', 'task.created')->exists()
        );
    }
}
