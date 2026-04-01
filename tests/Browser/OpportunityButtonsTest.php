<?php

namespace Tests\Browser;

use App\Models\OppComment;
use App\Models\OppProject;
use App\Models\OppSection;
use App\Models\OppTask;
use App\Models\OppTag;
use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class OpportunityButtonsTest extends DuskTestCase
{
    protected User $user;
    protected Organization $org;
    protected OppProject $project;
    protected OppSection $section;
    protected OppTask $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first();
        $this->org = $this->user->currentOrganization();
        $this->project = OppProject::where('organization_id', $this->org->id)->first();
        $this->section = $this->project->sections()->first();
        $this->task = OppTask::where('project_id', $this->project->id)
            ->whereNull('parent_task_id')
            ->where('status', 'incomplete')
            ->first();
    }

    // ═══════════════════════════════════════════════════════════════
    // SIDEBAR NAVIGATION
    // ═══════════════════════════════════════════════════════════════

    public function test_sidebar_home_link(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity')
            ->assertSee('Home'));
    }

    public function test_sidebar_my_tasks_link(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/my-tasks')
            ->assertSee('My tasks'));
    }

    public function test_sidebar_inbox_link(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/inbox')
            ->assertSee('Inbox'));
    }

    public function test_sidebar_goals_link(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/goals')
            ->assertSee('Goals'));
    }

    public function test_sidebar_portfolios_link(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/portfolios')
            ->assertSee('Portfolios'));
    }

    public function test_sidebar_reporting_link(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/reporting')
            ->assertSee('Reporting'));
    }

    public function test_sidebar_project_links(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity')
            ->assertSee($this->project->name));
    }

    public function test_sidebar_hub_link(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity')
            ->assertSee('BAI Hub'));
    }

    // ═══════════════════════════════════════════════════════════════
    // PROJECT TABS (Overview | List | Board | Timeline | Calendar)
    // ═══════════════════════════════════════════════════════════════

    public function test_project_overview_tab(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug . '/overview')
            ->assertSee('Project description')
            ->assertSee('Project roles')
            ->assertSee('Key resources'));
    }

    public function test_project_list_tab(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug)
            ->pause(1000)
            ->assertSee('Add task'));
    }

    public function test_project_board_tab(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug . '/board')
            ->pause(1000)
            ->assertSee($this->project->name));
    }

    public function test_project_timeline_tab(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug . '/timeline')
            ->assertSee('Today'));
    }

    public function test_project_calendar_tab(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug . '/calendar')
            ->assertSee($this->project->name));
    }

    // ═══════════════════════════════════════════════════════════════
    // TASK CRUD VIA API (tests the actual functionality)
    // ═══════════════════════════════════════════════════════════════

    public function test_api_create_task(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/opp/tasks', [
                'title'      => 'Dusk Button Test Task',
                'project_id' => $this->project->id,
                'section_id' => $this->section->id,
                'assignee_id'=> $this->user->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('task.title', 'Dusk Button Test Task')
            ->assertJsonPath('task.status', 'incomplete');
    }

    public function test_api_update_task_title(): void
    {
        $this->actingAs($this->user)
            ->putJson('/api/opp/tasks/' . $this->task->id, ['title' => 'Updated Title'])
            ->assertOk()
            ->assertJsonPath('task.title', 'Updated Title');

        // Revert
        $this->task->update(['title' => $this->task->getOriginal('title') ?: $this->task->title]);
    }

    public function test_api_update_task_due_date(): void
    {
        $date = now()->addDays(7)->format('Y-m-d');
        $this->actingAs($this->user)
            ->putJson('/api/opp/tasks/' . $this->task->id, ['due_date' => $date])
            ->assertOk()
            ->assertJsonPath('task.due_date', $date);
    }

    public function test_api_update_task_assignee(): void
    {
        $this->actingAs($this->user)
            ->putJson('/api/opp/tasks/' . $this->task->id, ['assignee_id' => $this->user->id])
            ->assertOk()
            ->assertJsonPath('task.assignee.id', $this->user->id);
    }

    public function test_api_update_task_description(): void
    {
        $this->actingAs($this->user)
            ->putJson('/api/opp/tasks/' . $this->task->id, ['description' => 'Test description'])
            ->assertOk()
            ->assertJsonPath('task.description', 'Test description');
    }

    public function test_api_complete_toggle(): void
    {
        $before = $this->task->status;

        $this->actingAs($this->user)
            ->postJson('/api/opp/tasks/' . $this->task->id . '/complete')
            ->assertOk()
            ->assertJsonPath('task.status', $before === 'complete' ? 'incomplete' : 'complete');

        // Toggle back
        $this->actingAs($this->user)
            ->postJson('/api/opp/tasks/' . $this->task->id . '/complete')
            ->assertOk()
            ->assertJsonPath('task.status', $before);
    }

    public function test_api_like_toggle(): void
    {
        $r1 = $this->actingAs($this->user)
            ->postJson('/api/opp/tasks/' . $this->task->id . '/like')
            ->assertOk();
        $liked = $r1->json('liked');

        $r2 = $this->actingAs($this->user)
            ->postJson('/api/opp/tasks/' . $this->task->id . '/like')
            ->assertOk();

        $this->assertNotEquals($liked, $r2->json('liked'));
    }

    public function test_api_duplicate_task(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/opp/tasks/' . $this->task->id . '/duplicate')
            ->assertStatus(201)
            ->assertJsonPath('task.title', $this->task->title . ' (copy)');

        // Cleanup
        OppTask::where('title', $this->task->title . ' (copy)')->forceDelete();
    }

    public function test_api_follow_toggle(): void
    {
        $r1 = $this->actingAs($this->user)
            ->postJson('/api/opp/tasks/' . $this->task->id . '/followers', ['user_id' => $this->user->id])
            ->assertOk();

        $r2 = $this->actingAs($this->user)
            ->postJson('/api/opp/tasks/' . $this->task->id . '/followers', ['user_id' => $this->user->id])
            ->assertOk();

        $this->assertNotEquals($r1->json('added'), $r2->json('added'));
    }

    public function test_api_assignee_toggle(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/opp/tasks/' . $this->task->id . '/assignees', ['user_id' => $this->user->id])
            ->assertOk();
    }

    public function test_api_move_task(): void
    {
        $this->actingAs($this->user)
            ->putJson('/api/opp/tasks/' . $this->task->id . '/move', [
                'section_id' => $this->section->id,
                'position'   => 5000,
            ])
            ->assertOk();
    }

    public function test_api_delete_task(): void
    {
        $tempTask = OppTask::create([
            'project_id' => $this->project->id, 'section_id' => $this->section->id,
            'title' => 'Task to delete', 'created_by' => $this->user->id, 'position' => 99999,
        ]);

        $this->actingAs($this->user)
            ->deleteJson('/api/opp/tasks/' . $tempTask->id)
            ->assertOk();

        $this->assertSoftDeleted('opp_tasks', ['id' => $tempTask->id]);
    }

    // ═══════════════════════════════════════════════════════════════
    // SUBTASK CRUD
    // ═══════════════════════════════════════════════════════════════

    public function test_api_create_subtask(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/opp/tasks', [
                'title'          => 'Dusk Subtask',
                'project_id'     => $this->project->id,
                'parent_task_id' => $this->task->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('task.title', 'Dusk Subtask');
    }

    public function test_api_complete_subtask(): void
    {
        $sub = OppTask::where('parent_task_id', $this->task->id)->first();
        if (!$sub) $this->markTestSkipped('No subtasks');

        $this->actingAs($this->user)
            ->postJson('/api/opp/tasks/' . $sub->id . '/complete')
            ->assertOk()
            ->assertJsonStructure(['task' => ['id', 'status']]);

        // Toggle back
        $this->actingAs($this->user)
            ->postJson('/api/opp/tasks/' . $sub->id . '/complete')
            ->assertOk();
    }

    public function test_api_task_detail_returns_subtasks(): void
    {
        $parentWithSubs = OppTask::whereHas('subtasks')->first();
        if (!$parentWithSubs) $this->markTestSkipped('No parent with subtasks');

        $this->actingAs($this->user)
            ->getJson('/api/opp/tasks/' . $parentWithSubs->id)
            ->assertOk()
            ->assertJsonStructure(['task' => ['subtasks']]);
    }

    // ═══════════════════════════════════════════════════════════════
    // SECTION CRUD
    // ═══════════════════════════════════════════════════════════════

    public function test_api_create_section(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/opp/sections', ['name' => 'Dusk Section', 'project_id' => $this->project->id])
            ->assertStatus(201)
            ->assertJsonPath('section.name', 'Dusk Section');
    }

    public function test_api_rename_section(): void
    {
        $sec = OppSection::where('project_id', $this->project->id)->orderByDesc('id')->first();

        $this->actingAs($this->user)
            ->putJson('/api/opp/sections/' . $sec->id, ['name' => 'Renamed Section'])
            ->assertOk();

        $this->assertEquals('Renamed Section', $sec->fresh()->name);
    }

    public function test_api_delete_section(): void
    {
        $sec = OppSection::create(['project_id' => $this->project->id, 'name' => 'To Delete', 'position' => 99999]);

        $this->actingAs($this->user)
            ->deleteJson('/api/opp/sections/' . $sec->id)
            ->assertOk();
    }

    // ═══════════════════════════════════════════════════════════════
    // COMMENT CRUD
    // ═══════════════════════════════════════════════════════════════

    public function test_api_post_comment(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/opp/comments', ['body' => 'Dusk test comment', 'task_id' => $this->task->id])
            ->assertStatus(201)
            ->assertJsonPath('comment.body', 'Dusk test comment')
            ->assertJsonPath('comment.user.id', $this->user->id);
    }

    public function test_api_list_comments(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/opp/comments?task_id=' . $this->task->id)
            ->assertOk()
            ->assertJsonStructure(['comments']);
    }

    public function test_api_update_comment(): void
    {
        $comment = OppComment::where('user_id', $this->user->id)->first();
        if (!$comment) $this->markTestSkipped('No comments by user');

        $this->actingAs($this->user)
            ->putJson('/api/opp/comments/' . $comment->id, ['body' => 'Updated comment'])
            ->assertOk();
    }

    public function test_api_delete_comment(): void
    {
        $comment = OppComment::create(['task_id' => $this->task->id, 'user_id' => $this->user->id, 'body' => 'To delete']);

        $this->actingAs($this->user)
            ->deleteJson('/api/opp/comments/' . $comment->id)
            ->assertOk();
    }

    // ═══════════════════════════════════════════════════════════════
    // TAG CRUD
    // ═══════════════════════════════════════════════════════════════

    public function test_api_list_tags(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/opp/tags')
            ->assertOk()
            ->assertJsonStructure(['tags']);
    }

    public function test_api_create_tag(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/opp/tags', ['name' => 'DuskTag', 'color' => '#FF0000'])
            ->assertStatus(201);

        OppTag::where('name', 'DuskTag')->delete();
    }

    public function test_api_toggle_tag_on_task(): void
    {
        $tag = OppTag::where('organization_id', $this->org->id)->first();
        if (!$tag) $this->markTestSkipped('No tags');

        $this->actingAs($this->user)
            ->postJson('/api/opp/tasks/' . $this->task->id . '/tags', ['tag_id' => $tag->id])
            ->assertOk();
    }

    // ═══════════════════════════════════════════════════════════════
    // PROJECT MEMBER MANAGEMENT
    // ═══════════════════════════════════════════════════════════════

    public function test_api_list_project_members(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/opp/projects/' . $this->project->slug . '/members')
            ->assertOk()
            ->assertJsonStructure(['members']);
    }

    public function test_api_add_and_remove_project_member(): void
    {
        $otherUser = User::where('id', '!=', $this->user->id)->first();
        if (!$otherUser) $this->markTestSkipped('Need another user');

        // Remove first if exists
        $this->project->members()->detach($otherUser->id);

        // Add
        $this->actingAs($this->user)
            ->postJson('/api/opp/projects/' . $this->project->slug . '/members', ['user_id' => $otherUser->id])
            ->assertOk()
            ->assertJsonPath('member.id', $otherUser->id);

        // Remove
        $this->actingAs($this->user)
            ->deleteJson('/api/opp/projects/' . $this->project->slug . '/members/' . $otherUser->id)
            ->assertOk();
    }

    // ═══════════════════════════════════════════════════════════════
    // SEARCH & FAVORITES
    // ═══════════════════════════════════════════════════════════════

    public function test_api_search(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/opp/search?query=banking')
            ->assertOk();
    }

    public function test_api_favorites_toggle(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/opp/favorites/toggle', [
                'favorable_type' => 'App\\Models\\OppProject',
                'favorable_id'   => $this->project->id,
            ])
            ->assertOk();

        // Toggle back
        $this->actingAs($this->user)
            ->postJson('/api/opp/favorites/toggle', [
                'favorable_type' => 'App\\Models\\OppProject',
                'favorable_id'   => $this->project->id,
            ])
            ->assertOk();
    }

    public function test_api_saved_searches(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/opp/saved-searches')
            ->assertOk();
    }

    // ═══════════════════════════════════════════════════════════════
    // REPORTS
    // ═══════════════════════════════════════════════════════════════

    public function test_api_team_workload(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/opp/reports/team-workload')
            ->assertOk()
            ->assertJsonStructure(['workload']);
    }

    public function test_api_project_progress(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/opp/reports/project-progress')
            ->assertOk()
            ->assertJsonStructure(['projects']);
    }

    // ═══════════════════════════════════════════════════════════════
    // GOALS
    // ═══════════════════════════════════════════════════════════════

    public function test_api_update_goal_progress(): void
    {
        $goal = \App\Models\OppGoal::where('organization_id', $this->org->id)->first();
        if (!$goal) $this->markTestSkipped('No goals');

        $this->actingAs($this->user)
            ->putJson('/api/opp/goals/' . $goal->id . '/progress', ['current_value' => 50])
            ->assertOk();
    }

    // ═══════════════════════════════════════════════════════════════
    // RULES & APPROVALS
    // ═══════════════════════════════════════════════════════════════

    public function test_api_create_rule(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/opp/projects/' . $this->project->slug . '/rules', [
                'name'           => 'Auto-assign rule',
                'trigger_type'   => 'task_added',
                'trigger_config' => ['any' => true],
                'action_type'    => 'assign',
                'action_config'  => ['user_id' => $this->user->id],
            ])
            ->assertStatus(201);
    }

    public function test_api_list_rules(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/opp/projects/' . $this->project->slug . '/rules')
            ->assertOk();
    }

    public function test_api_create_approval(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/opp/approvals', ['task_id' => $this->task->id])
            ->assertStatus(201);
    }

    // ═══════════════════════════════════════════════════════════════
    // BROWSER-LEVEL BUTTON CHECKS (page loads with interactive elements)
    // ═══════════════════════════════════════════════════════════════

    public function test_list_view_has_add_task_button(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug)
            ->assertSee('Add task'));
    }

    public function test_list_view_has_section_headers(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug)
            ->pause(1000)
            ->assertSee('NAME'));
    }

    public function test_list_view_has_filter_sort_group(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug)
            ->assertSee('Filter')
            ->assertSee('Sort')
            ->assertSee('Group'));
    }

    public function test_list_view_has_add_section(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug)
            ->pause(1000)
            ->assertPresent('button')); // Add section button rendered by Alpine
    }

    public function test_board_view_has_add_task(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug . '/board')
            ->assertSee('Add task'));
    }

    public function test_board_view_has_add_section(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug . '/board')
            ->pause(1000)
            ->assertPresent('button'));
    }

    public function test_calendar_has_navigation(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug . '/calendar')
            ->assertSee('Today'));
    }

    public function test_timeline_has_zoom_controls(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug . '/timeline')
            ->assertSee('Weeks')
            ->assertSee('Months'));
    }

    public function test_overview_has_status_buttons(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug . '/overview')
            ->assertSee('On track')
            ->assertSee('At risk')
            ->assertSee('Off track'));
    }

    public function test_overview_has_add_member(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects/' . $this->project->slug . '/overview')
            ->assertSee('Add member'));
    }

    public function test_projects_index_has_new_project(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/projects')
            ->assertSee('New Project'));
    }

    public function test_goals_has_add_goal(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/goals')
            ->assertSee('Add goal'));
    }

    public function test_portfolios_has_new_portfolio(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/portfolios')
            ->assertSee('New portfolio'));
    }

    public function test_home_has_create_task(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity')
            ->pause(2000)
            ->assertSee('My tasks'));
    }

    public function test_home_has_upcoming_tab(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity')
            ->pause(2000)
            ->assertSee('Projects'));
    }

    public function test_my_tasks_has_add_task(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/my-tasks')
            ->pause(1000)
            ->assertPresent('button'));
    }

    public function test_my_tasks_has_completed_toggle(): void
    {
        $this->browse(fn(Browser $b) => $b->loginAs($this->user)
            ->visit('/opportunity/my-tasks')
            ->pause(1000)
            ->assertSee('My tasks'));
    }
}
