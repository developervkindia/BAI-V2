<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectBillingWeek;
use App\Models\ProjectTimeLog;
use App\Models\TimesheetSubmission;
use App\Models\UserCapacity;
use App\Services\ProjectReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $org = $user->currentOrganization();

        $projects = Project::where('organization_id', $org->id)
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhere('visibility', 'organization')
                    ->orWhereHas('members', fn ($q) => $q->where('user_id', $user->id));
            })
            ->withCount(['tasks as total_tasks' => fn ($q) => $q->whereNull('parent_task_id')])
            ->withCount(['tasks as completed_tasks' => fn ($q) => $q->whereNull('parent_task_id')->where('is_completed', true)])
            ->with(['owner', 'members' => fn ($q) => $q->limit(5), 'client'])
            ->latest()
            ->get();

        $projectIds = $projects->pluck('id');

        $totalRevenue = ProjectBillingWeek::whereIn('project_id', $projectIds)
            ->whereNotNull('locked_at')
            ->sum('total_amount');

        $totalHours = DB::table('project_time_logs')
            ->join('project_tasks', 'project_time_logs.project_task_id', '=', 'project_tasks.id')
            ->whereIn('project_tasks.project_id', $projectIds)
            ->sum('project_time_logs.hours');

        $memberCount = DB::table('project_members')
            ->whereIn('project_id', $projectIds)
            ->distinct('user_id')
            ->count('user_id');

        $workload = DB::table('project_tasks')
            ->join('users', 'project_tasks.assignee_id', '=', 'users.id')
            ->whereIn('project_tasks.project_id', $projectIds)
            ->whereNull('project_tasks.parent_task_id')
            ->whereNull('project_tasks.deleted_at')
            ->where('project_tasks.is_completed', false)
            ->whereNotNull('project_tasks.assignee_id')
            ->select('users.id', 'users.name', DB::raw('count(*) as task_count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('task_count')
            ->limit(6)
            ->get();

        $todaysTasks = DB::table('project_tasks')
            ->join('projects', 'project_tasks.project_id', '=', 'projects.id')
            ->where('project_tasks.assignee_id', $user->id)
            ->whereIn('project_tasks.project_id', $projectIds)
            ->whereNull('project_tasks.parent_task_id')
            ->whereNull('project_tasks.deleted_at')
            ->where('project_tasks.is_completed', false)
            ->whereNotNull('project_tasks.due_date')
            ->where('project_tasks.due_date', '<=', now()->toDateString())
            ->select(
                'project_tasks.id', 'project_tasks.title', 'project_tasks.status',
                'project_tasks.priority', 'project_tasks.due_date',
                'projects.name as project_name', 'projects.color as project_color',
                'projects.slug as project_slug'
            )
            ->orderBy('project_tasks.due_date')
            ->limit(10)
            ->get();

        return view('projects.index', [
            'projects' => $projects,
            'organization' => $org,
            'workspaces' => collect(),
            'totalRevenue' => $totalRevenue,
            'totalHours' => round($totalHours, 1),
            'memberCount' => $memberCount,
            'workload' => $workload,
            'todaysTasks' => $todaysTasks,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $org = $user->currentOrganization();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => 'in:not_started,in_progress,on_hold,completed,cancelled',
            'priority' => 'in:none,low,medium,high,critical',
            'color' => 'nullable|string|max:20',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'project_type' => 'in:fixed,billing',
            'client_id' => 'nullable|integer|exists:clients,id',
            'budget' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'srs_url' => 'nullable|url|max:500',
            'design_url' => 'nullable|url|max:500',
        ]);

        $project = Project::create(array_merge($validated, [
            'organization_id' => $org->id,
            'owner_id' => $user->id,
            'slug' => Str::slug($validated['name']).'-'.Str::random(6),
            'color' => $validated['color'] ?? '#6366f1',
        ]));

        $project->members()->attach($user->id, ['role' => 'manager']);
        $project->taskLists()->create(['name' => 'Tasks', 'position' => 1000]);

        return redirect()->route('projects.show', $project)->with('success', 'Project created!');
    }

    public function show(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load([
            'statuses', 'customFields',
            'taskLists.tasks' => fn ($q) => $q->whereNull('parent_task_id')
                ->whereNull('deleted_at')
                ->with(['assignee', 'labels', 'projectStatus', 'subtasks' => fn ($q) => $q->whereNull('deleted_at')]),
            'members',
            'milestones',
            'labels',
            'owner',
        ]);

        $canEdit = $project->canEdit($request->user());

        return view('projects.show', [
            'project' => $project,
            'canEdit' => $canEdit,
            'workspaces' => collect(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        abort_unless($project->canEdit($request->user()), 403);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => 'sometimes|in:not_started,in_progress,on_hold,completed,cancelled',
            'priority' => 'sometimes|in:none,low,medium,high,critical',
            'color' => 'nullable|string|max:20',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'design_status' => 'sometimes|in:none,pending,approved,rejected',
            'design_feedback' => 'nullable|string|max:2000',
            'srs_url' => 'nullable|url|max:500',
            'design_url' => 'nullable|url|max:500',
            'hourly_rate' => 'nullable|numeric|min:0',
            'budget' => 'nullable|numeric|min:0',
        ]);

        if (isset($validated['design_status']) && in_array($validated['design_status'], ['approved', 'rejected'])) {
            $validated['design_approved_by'] = $request->user()->id;
            $validated['design_approved_at'] = now();
        }

        $project->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'project' => $project]);
        }

        return back()->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        abort_unless($project->isManager(auth()->user()), 403);

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    public function board(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load([
            'statuses', 'customFields',
            'tasks' => fn ($q) => $q->whereNull('parent_task_id')
                ->whereNull('deleted_at')
                ->with(['assignee', 'taskList', 'labels', 'projectStatus']),
            'members',
            'milestones',
            'labels',
            'taskLists',
        ]);

        $canEdit = $project->canEdit($request->user());

        $tasks = $project->tasks->map(fn ($t) => [
            'id' => $t->id,
            'title' => $t->title,
            'status' => $t->status,
            'project_status_id' => $t->project_status_id,
            'status_name' => $t->status_name,
            'status_color' => $t->status_color,
            'priority' => $t->priority,
            'issue_type' => $t->issue_type ?? 'task',
            'story_points' => $t->story_points,
            'is_completed' => (bool) $t->is_completed,
            'due_date' => $t->due_date?->format('Y-m-d'),
            'parent_task_id' => $t->parent_task_id,
            'subtasks_count' => 0,
            'task_list' => $t->taskList ? ['id' => $t->taskList->id, 'name' => $t->taskList->name] : null,
            'assignee' => $t->assignee ? ['id' => $t->assignee->id, 'name' => $t->assignee->name] : null,
            'assignee_id' => $t->assignee_id,
            'labels' => $t->labels->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'color' => $l->color])->values(),
        ]);

        return view('projects.board', [
            'project' => $project,
            'canEdit' => $canEdit,
            'tasks' => $tasks,
            'workspaces' => collect(),
        ]);
    }

    public function milestones(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load(['milestones.tasks.assignee', 'milestones.tasks.projectStatus', 'members', 'statuses']);

        $canEdit = $project->canEdit($request->user());

        return view('projects.milestones', [
            'project' => $project,
            'canEdit' => $canEdit,
            'workspaces' => collect(),
        ]);
    }

    public function overview(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load(['members', 'labels', 'milestones.tasks', 'taskLists', 'statuses', 'owner', 'client', 'organization']);

        $canEdit = $project->canEdit($request->user());

        // Task counts by status (using custom project_status_id)
        $taskStats = DB::table('project_tasks')
            ->where('project_tasks.project_id', $project->id)
            ->whereNull('project_tasks.parent_task_id')
            ->whereNull('project_tasks.deleted_at')
            ->join('project_statuses', 'project_tasks.project_status_id', '=', 'project_statuses.id')
            ->selectRaw('project_statuses.name as status_name, project_statuses.color as status_color, project_statuses.slug as status, count(*) as cnt')
            ->groupBy('project_statuses.id', 'project_statuses.name', 'project_statuses.color', 'project_statuses.slug')
            ->get()
            ->keyBy('status');

        // Task counts by priority
        $priorityStats = DB::table('project_tasks')
            ->where('project_id', $project->id)
            ->whereNull('parent_task_id')
            ->whereNull('deleted_at')
            ->selectRaw('priority, count(*) as cnt')
            ->groupBy('priority')
            ->pluck('cnt', 'priority');

        // Upcoming tasks (next 7 days)
        $upcoming = $project->tasks()
            ->whereNull('parent_task_id')->whereNull('deleted_at')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->where('is_completed', false)
            ->with('assignee')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Overdue tasks
        $overdue = $project->tasks()
            ->whereNull('parent_task_id')->whereNull('deleted_at')
            ->where('due_date', '<', now()->startOfDay())
            ->where('is_completed', false)
            ->with('assignee')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Recent activity (last 25 entries)
        $taskIds = $project->tasks()->pluck('id');
        $activities = ProjectActivity::whereIn('project_task_id', $taskIds)
            ->with('user', 'task')
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        return view('projects.overview', compact('project', 'canEdit', 'taskStats', 'priorityStats', 'upcoming', 'overdue', 'activities'));
    }

    public function timeline(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load([
            'statuses',
            'taskLists.tasks' => fn ($q) => $q->whereNull('parent_task_id')->whereNull('deleted_at')->with(['assignee', 'projectStatus']),
            'members',
            'labels',
        ]);

        $canEdit = $project->canEdit($request->user());

        return view('projects.timeline', compact('project', 'canEdit'));
    }

    public function calendar(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load(['statuses']);

        $tasks = $project->tasks()
            ->whereNull('parent_task_id')
            ->whereNull('deleted_at')
            ->whereNotNull('due_date')
            ->with(['assignee', 'taskList', 'projectStatus'])
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'status' => $t->status,
                'project_status_id' => $t->project_status_id,
                'status_name' => $t->status_name,
                'status_color' => $t->status_color,
                'priority' => $t->priority,
                'issue_type' => $t->issue_type ?? 'task',
                'due_date' => $t->due_date->format('Y-m-d'),
                'is_completed' => $t->is_completed,
                'assignee' => $t->assignee ? ['name' => $t->assignee->name] : null,
            ]);

        $canEdit = $project->canEdit($request->user());

        return view('projects.calendar', compact('project', 'tasks', 'canEdit'));
    }

    public function updates(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load(['members']);
        $updates = $project->weeklyUpdates()
            ->with(['author', 'qaApprover'])
            ->orderByDesc('week_start')
            ->get();

        $canEdit = $project->canEdit($request->user());

        return view('projects.updates', compact('project', 'canEdit', 'updates'));
    }

    public function scope(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load(['members']);
        $changes = $project->scopeChanges()
            ->with(['requester', 'approver'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('status');

        $canEdit = $project->canEdit($request->user());

        return view('projects.scope', compact('project', 'canEdit', 'changes'));
    }

    public function billing(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load(['members', 'client']);
        $billingWeeks = $project->billingWeeks()
            ->with(['entries.user', 'locker'])
            ->orderByDesc('week_start')
            ->get();

        $canEdit = $project->canEdit($request->user());

        return view('projects.billing', compact('project', 'canEdit', 'billingWeeks'));
    }

    public function billingInvoice(Request $request, Project $project, ProjectBillingWeek $week)
    {
        abort_unless($project->canAccess($request->user()), 403);
        abort_unless($week->project_id === $project->id, 404);

        $week->load(['entries.user', 'locker']);
        $project->load(['client', 'owner']);

        $taskIds = $project->tasks()->pluck('id');
        $timeLogs = ProjectTimeLog::whereIn('project_task_id', $taskIds)
            ->whereBetween('logged_at', [$week->week_start, $week->week_end])
            ->with(['task:id,title', 'user:id,name'])
            ->orderBy('user_id')
            ->orderBy('logged_at')
            ->get();

        // Group time logs by user_id
        $logsByUser = $timeLogs->groupBy('user_id');

        return view('projects.billing-invoice', compact('project', 'week', 'timeLogs', 'logsByUser'));
    }

    public function backlog(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load(['sprints.tasks.projectStatus', 'taskLists', 'members', 'labels', 'statuses']);

        $sprintTaskIds = $project->sprints
            ->flatMap(fn ($s) => $s->tasks->pluck('id'))
            ->unique();

        $backlogTasks = $project->tasks()
            ->whereNull('parent_task_id')
            ->whereNull('deleted_at')
            ->whereNotIn('id', $sprintTaskIds->isEmpty() ? [-1] : $sprintTaskIds)
            ->where('is_completed', false)
            ->with(['assignee', 'taskList', 'labels', 'projectStatus'])
            ->orderByRaw("FIELD(priority,'critical','high','medium','low','none')")
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'status' => $t->status,
                'project_status_id' => $t->project_status_id,
                'status_name' => $t->status_name,
                'status_color' => $t->status_color,
                'priority' => $t->priority,
                'issue_type' => $t->issue_type ?? 'task',
                'story_points' => $t->story_points,
                'due_date' => $t->due_date?->format('Y-m-d'),
                'assignee_id' => $t->assignee_id,
                'assignee' => $t->assignee ? ['id' => $t->assignee->id, 'name' => $t->assignee->name] : null,
                'task_list' => $t->taskList ? ['id' => $t->taskList->id, 'name' => $t->taskList->name] : null,
                'labels' => $t->labels->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'color' => $l->color])->values(),
                'position' => $t->position,
                'is_completed' => $t->is_completed,
                'task_list_id' => $t->task_list_id,
            ]);

        $sprints = $project->sprints->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'start_date' => $s->start_date?->format('Y-m-d'),
            'end_date' => $s->end_date?->format('Y-m-d'),
            'status' => $s->status,
            'tasks' => $s->tasks->whereNull('deleted_at')->map(fn ($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'status' => $t->status,
                'project_status_id' => $t->project_status_id,
                'status_name' => $t->status_name,
                'status_color' => $t->status_color,
                'priority' => $t->priority,
                'issue_type' => $t->issue_type ?? 'task',
                'story_points' => $t->story_points,
                'is_completed' => $t->is_completed,
                'assignee_id' => $t->assignee_id,
            ])->values(),
        ]);

        $canEdit = $project->canEdit($request->user());

        return view('projects.backlog', compact('project', 'canEdit', 'backlogTasks', 'sprints'));
    }

    public function timesheets(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load(['members', 'statuses']);
        $canEdit = $project->canEdit($request->user());

        $weekStart = $request->input('week_start', now()->startOfWeek()->toDateString());
        $weekEnd = Carbon::parse($weekStart)->endOfWeek()->toDateString();
        $userId = $project->isManager($request->user())
            ? ($request->input('user_id', $request->user()->id))
            : $request->user()->id;

        $taskIds = $project->tasks()->pluck('id');
        $logs = ProjectTimeLog::whereIn('project_task_id', $taskIds)
            ->where('user_id', $userId)
            ->whereBetween('logged_at', [$weekStart, $weekEnd])
            ->with('task:id,title')
            ->get();

        // Build grid: rows = tasks, columns = days
        $tasks = $logs->groupBy('project_task_id')->map(function ($taskLogs) {
            $task = $taskLogs->first()->task;
            $days = [];
            foreach ($taskLogs as $log) {
                $day = $log->logged_at->format('Y-m-d');
                $days[$day] = ($days[$day] ?? 0) + $log->hours;
            }

            return [
                'task_id' => $task->id,
                'title' => $task->title,
                'days' => $days,
                'total' => round(array_sum($days), 2),
            ];
        })->values();

        $dailyTotals = [];
        for ($d = Carbon::parse($weekStart); $d->lte(Carbon::parse($weekEnd)); $d->addDay()) {
            $dayStr = $d->format('Y-m-d');
            $dailyTotals[$dayStr] = round($logs->where('logged_at', $d->copy())->sum('hours'), 2);
        }

        // Timesheet submission status
        $submission = TimesheetSubmission::where('project_id', $project->id)
            ->where('user_id', $userId)
            ->where('week_start', $weekStart)
            ->first();

        return view('projects.timesheets', compact(
            'project', 'canEdit', 'weekStart', 'weekEnd',
            'userId', 'tasks', 'dailyTotals', 'submission', 'logs'
        ));
    }

    public function budget(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load(['members', 'statuses']);
        $canEdit = $project->canEdit($request->user());

        $taskIds = $project->tasks()->pluck('id');

        $totalLoggedHours = ProjectTimeLog::whereIn('project_task_id', $taskIds)->sum('hours');
        $billableHours = ProjectTimeLog::whereIn('project_task_id', $taskIds)->where('is_billable', true)->sum('hours');
        $totalEstimatedHours = $project->tasks()->whereNull('parent_task_id')->whereNull('deleted_at')->sum('estimated_hours');

        $rate = (float) ($project->hourly_rate ?? 0);
        $budget = (float) ($project->budget ?? 0);
        $actualSpend = $totalLoggedHours * $rate;
        $remaining = $budget - $actualSpend;
        $budgetUsedPct = $budget > 0 ? round(($actualSpend / $budget) * 100, 1) : 0;

        // Weekly burn trend (last 8 weeks)
        $weeklySpend = ProjectTimeLog::whereIn('project_task_id', $taskIds)
            ->where('logged_at', '>=', now()->subWeeks(8)->startOfWeek())
            ->selectRaw('YEARWEEK(logged_at, 1) as week_key, SUM(hours) as total_hours')
            ->groupBy('week_key')
            ->orderBy('week_key')
            ->get();

        // Forecast
        $recentHours = ProjectTimeLog::whereIn('project_task_id', $taskIds)
            ->where('logged_at', '>=', now()->subWeeks(4))
            ->sum('hours');
        $weeklyBurnRate = $recentHours / 4;
        $weeklyBurnCost = $weeklyBurnRate * $rate;

        $remainingEstimated = $project->tasks()
            ->whereNull('parent_task_id')->whereNull('deleted_at')
            ->where('is_completed', false)
            ->sum('estimated_hours');

        $weeksUntilBudgetExhausted = $weeklyBurnCost > 0 ? round($remaining / $weeklyBurnCost, 1) : null;
        $estimatedCompletionWeeks = $weeklyBurnRate > 0 ? round($remainingEstimated / $weeklyBurnRate, 1) : null;
        $estimatedTotalCost = $actualSpend + ($remainingEstimated * $rate);

        return view('projects.budget', compact(
            'project', 'canEdit',
            'totalLoggedHours', 'billableHours', 'totalEstimatedHours',
            'budget', 'actualSpend', 'remaining', 'budgetUsedPct', 'rate',
            'weeklySpend', 'weeklyBurnRate', 'weeklyBurnCost',
            'weeksUntilBudgetExhausted', 'estimatedCompletionWeeks', 'estimatedTotalCost',
            'remainingEstimated'
        ));
    }

    public function resources(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load(['members', 'statuses']);
        $canEdit = $project->canEdit($request->user());

        $memberData = $project->members->map(function ($member) use ($project) {
            $openTasks = $project->tasks()
                ->where('assignee_id', $member->id)
                ->whereNull('parent_task_id')
                ->whereNull('deleted_at')
                ->where('is_completed', false)
                ->with('taskList')
                ->get();

            $capacity = UserCapacity::getForUser($member->id, $project->id);

            return [
                'user' => ['id' => $member->id, 'name' => $member->name],
                'role' => $member->pivot->role,
                'tasks' => $openTasks->map(fn ($t) => [
                    'id' => $t->id,
                    'title' => $t->title,
                    'priority' => $t->priority,
                    'due_date' => $t->due_date?->format('M j'),
                    'estimated_hours' => $t->estimated_hours,
                ]),
                'total_estimated' => round($openTasks->sum('estimated_hours'), 1),
                'total_logged' => round(ProjectTimeLog::where('user_id', $member->id)
                    ->whereIn('project_task_id', $openTasks->pluck('id'))
                    ->sum('hours'), 1),
                'capacity' => $capacity?->weekly_capacity_hours ?? 40,
            ];
        });

        $unassigned = $project->tasks()
            ->whereNull('assignee_id')
            ->whereNull('parent_task_id')
            ->whereNull('deleted_at')
            ->where('is_completed', false)
            ->count();

        return view('projects.resources', compact('project', 'canEdit', 'memberData', 'unassigned'));
    }

    public function workload(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load(['members', 'statuses']);
        $canEdit = $project->canEdit($request->user());

        $weekStart = Carbon::parse($request->input('week_start', now()->startOfWeek()))->toDateString();
        $weekEnd = Carbon::parse($weekStart)->endOfWeek()->toDateString();

        $taskIds = $project->tasks()->pluck('id');
        $memberData = $project->members->map(function ($member) use ($project, $taskIds, $weekStart, $weekEnd) {
            $capacity = UserCapacity::getForUser($member->id, $project->id);
            $capHours = $capacity?->weekly_capacity_hours ?? 40;

            $allocated = $project->tasks()
                ->where('assignee_id', $member->id)
                ->whereNull('parent_task_id')->whereNull('deleted_at')
                ->where('is_completed', false)
                ->sum('estimated_hours');

            $logged = ProjectTimeLog::whereIn('project_task_id', $taskIds)
                ->where('user_id', $member->id)
                ->whereBetween('logged_at', [$weekStart, $weekEnd])
                ->sum('hours');

            $dailyHours = [];
            $d = Carbon::parse($weekStart);
            while ($d->lte(Carbon::parse($weekEnd))) {
                $dayStr = $d->format('Y-m-d');
                $dailyHours[$dayStr] = round(ProjectTimeLog::whereIn('project_task_id', $taskIds)
                    ->where('user_id', $member->id)
                    ->where('logged_at', $dayStr)
                    ->sum('hours'), 1);
                $d->addDay();
            }

            $utilPct = $capHours > 0 ? round(($logged / $capHours) * 100, 1) : 0;

            return [
                'user' => ['id' => $member->id, 'name' => $member->name],
                'capacity' => $capHours,
                'allocated' => round($allocated, 1),
                'logged' => round($logged, 1),
                'util_pct' => $utilPct,
                'daily' => $dailyHours,
            ];
        });

        return view('projects.workload', compact('project', 'canEdit', 'memberData', 'weekStart', 'weekEnd'));
    }

    public function chat(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);
        $project->load(['members', 'statuses']);
        $canEdit = $project->canEdit($request->user());

        return view('projects.chat', compact('project', 'canEdit'));
    }

    public function documents(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);
        $project->load(['members', 'statuses']);
        $canEdit = $project->canEdit($request->user());

        return view('projects.documents', compact('project', 'canEdit'));
    }

    public function recycleBin(Request $request, Project $project)
    {
        abort_unless($project->isManager($request->user()), 403);
        $project->load(['statuses']);
        $canEdit = true;

        return view('projects.recycle-bin', compact('project', 'canEdit'));
    }

    public function reports(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $project->load(['members', 'milestones', 'sprints', 'statuses']);
        $canEdit = $project->canEdit($request->user());

        $reportType = $request->input('report', 'task-progress');
        $from = Carbon::parse($request->input('date_from', now()->subDays(30)));
        $to = Carbon::parse($request->input('date_to', now()));

        $svc = new ProjectReportService;
        $reportData = [];

        switch ($reportType) {
            case 'task-progress':
                $reportData = [
                    'completion' => $svc->taskCompletionOverTime($project, $from, $to),
                    'byAssignee' => $svc->tasksByAssignee($project),
                    'byMilestone' => $svc->tasksByMilestone($project),
                ];
                break;
            case 'time-tracking':
                $reportData = [
                    'byUser' => $svc->hoursByUser($project, $from, $to),
                    'byDate' => $svc->hoursByDate($project, $from, $to),
                    'byTask' => $svc->hoursByTask($project, $from, $to),
                ];
                break;
            case 'milestones':
                $reportData = [
                    'milestones' => $svc->milestoneProgress($project),
                ];
                break;
            case 'burndown':
                $sprintId = $request->input('sprint_id');
                $sprint = $project->sprints->find($sprintId);
                if ($sprint) {
                    $reportData = ['burndown' => $svc->sprintBurndown($sprint)];
                }
                break;
        }

        return view('projects.reports', compact(
            'project', 'canEdit', 'reportType', 'reportData', 'from', 'to'
        ));
    }
}
