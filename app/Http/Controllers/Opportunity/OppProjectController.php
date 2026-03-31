<?php

namespace App\Http\Controllers\Opportunity;

use App\Http\Controllers\Controller;
use App\Models\OppProject;
use App\Models\OppSection;
use Illuminate\Http\Request;

class OppProjectController extends Controller
{
    /**
     * List all projects for the current organization.
     */
    public function index()
    {
        $user = auth()->user();
        $org = $user->currentOrganization();

        $projects = OppProject::where('organization_id', $org->id)
            ->where('is_template', false)
            ->where('status', '!=', 'archived')
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => function ($q) {
                    $q->where('status', 'complete');
                },
            ])
            ->with('owner')
            ->orderBy('name')
            ->get();

        return view('opportunity.projects.index', compact('projects', 'org'));
    }

    /**
     * Store a new project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'visibility' => 'nullable|string|in:public,private,org',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = auth()->user();
        $org = $user->currentOrganization();

        $project = OppProject::create(array_merge($validated, [
            'organization_id' => $org->id,
            'owner_id' => $user->id,
        ]));

        // Create default section
        OppSection::create([
            'name' => 'Untitled section',
            'project_id' => $project->id,
            'position' => 1000,
        ]);

        // Add owner as member
        $project->members()->attach($user->id, ['role' => 'owner']);

        return redirect()->route('opportunity.projects.show', $project);
    }

    /**
     * Show project in list view (default).
     */
    public function show(OppProject $project)
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $project->load([
            'sections' => fn($q) => $q->orderBy('position'),
            'sections.tasks' => fn($q) => $q->whereNull('parent_task_id')->orderBy('position'),
            'sections.tasks.assignee',
            'sections.tasks.assignees',
            'members',
        ]);

        // Load counts for each task
        $project->sections->each(function ($section) {
            $section->tasks->each(function ($task) {
                $task->loadCount(['subtasks', 'comments', 'attachments']);
            });
        });

        return view('opportunity.projects.list', compact('project'));
    }

    /**
     * Update a project.
     */
    public function update(Request $request, OppProject $project)
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'visibility' => 'nullable|string|in:public,private,org',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'is_archived' => 'nullable|boolean',
        ]);

        $project->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['project' => $project->fresh()]);
        }

        return back()->with('success', 'Project updated.');
    }

    /**
     * Soft delete a project.
     */
    public function destroy(OppProject $project)
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $project->delete();

        return redirect()->route('opportunity.projects.index')
            ->with('success', 'Project moved to trash.');
    }

    public function overview(OppProject $project)
    {
        abort_unless($project->canAccess(auth()->user()), 403);
        $project->load(['owner', 'members', 'organization.members']);
        $project->loadCount([
            'tasks',
            'tasks as completed_tasks_count' => fn($q) => $q->where('status', 'complete'),
            'tasks as overdue_tasks_count' => fn($q) => $q->where('status', 'incomplete')->whereNotNull('due_date')->where('due_date', '<', now()->toDateString()),
        ]);
        return view('opportunity.projects.overview', compact('project'));
    }

    public function board(OppProject $project)
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $project->load([
            'sections' => fn($q) => $q->orderBy('position'),
            'sections.tasks' => fn($q) => $q->whereNull('parent_task_id')->where('status', '!=', 'complete')->orderBy('position'),
            'sections.tasks.assignee',
            'members',
        ]);

        $project->sections->each(fn($s) => $s->tasks->each(fn($t) => $t->loadCount(['subtasks', 'comments'])));

        return view('opportunity.projects.board', compact('project'));
    }

    /**
     * Show project in timeline view.
     */
    public function timeline(OppProject $project)
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $project->load([
            'sections.tasks' => function ($q) {
                $q->orderBy('position');
            },
            'sections.tasks.assignee',
            'members',
        ]);

        return view('opportunity.projects.timeline', compact('project'));
    }

    /**
     * Show project in calendar view.
     */
    public function calendar(OppProject $project)
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $project->load([
            'tasks' => function ($q) {
                $q->whereNotNull('due_date')->with('assignee');
            },
            'members',
        ]);

        return view('opportunity.projects.calendar', compact('project'));
    }
}
