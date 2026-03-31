<?php

namespace App\Http\Controllers\Opportunity;

use App\Http\Controllers\Controller;
use App\Models\OppActivityLog;
use App\Models\OppProject;
use App\Models\OppTask;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OppDashboardController extends Controller
{
    /**
     * Home dashboard: user's assigned tasks grouped by urgency.
     */
    public function home()
    {
        $user = auth()->user();
        $org = $user->currentOrganization();

        $tasks = OppTask::where('assignee_id', $user->id)
            ->whereHas('project', function ($q) use ($org) {
                $q->where('organization_id', $org->id);
            })
            ->where('status', '!=', 'complete')
            ->orderBy('due_date', 'asc')
            ->with(['project', 'section'])
            ->get();

        $today = Carbon::today();
        $endOfWeek = Carbon::now()->endOfWeek();

        $grouped = [
            'Overdue' => $tasks->filter(fn ($t) => $t->due_date && Carbon::parse($t->due_date)->lt($today)),
            'Today' => $tasks->filter(fn ($t) => $t->due_date && Carbon::parse($t->due_date)->isToday()),
            'Upcoming this week' => $tasks->filter(fn ($t) => $t->due_date && Carbon::parse($t->due_date)->gt($today) && Carbon::parse($t->due_date)->lte($endOfWeek)),
            'Later' => $tasks->filter(fn ($t) => !$t->due_date || Carbon::parse($t->due_date)->gt($endOfWeek)),
        ];

        $projects = OppProject::where('organization_id', $org->id)
            ->where('is_template', false)
            ->where('status', '!=', 'archived')
            ->withCount('tasks')
            ->with('owner')
            ->latest()
            ->limit(4)
            ->get();

        $teamMembers = $org->members()->limit(6)->get();
        $completedCount = OppTask::where('assignee_id', $user->id)
            ->where('status', 'complete')
            ->where('completed_at', '>=', now()->startOfWeek())
            ->count();

        return view('opportunity.home', compact('tasks', 'projects', 'teamMembers', 'completedCount'));
    }

    /**
     * My Tasks view with extended grouping options.
     */
    public function myTasks()
    {
        $user = auth()->user();
        $org = $user->currentOrganization();

        $tasks = OppTask::where('assignee_id', $user->id)
            ->whereHas('project', function ($q) use ($org) {
                $q->where('organization_id', $org->id);
            })
            ->where('status', '!=', 'complete')
            ->orderBy('due_date', 'asc')
            ->with(['project', 'section', 'tags'])
            ->get();

        $today = Carbon::today();
        $endOfWeek = Carbon::now()->endOfWeek();
        $endOfNextWeek = Carbon::now()->addWeek()->endOfWeek();

        $grouped = [
            'Overdue' => $tasks->filter(fn ($t) => $t->due_date && Carbon::parse($t->due_date)->lt($today)),
            'Today' => $tasks->filter(fn ($t) => $t->due_date && Carbon::parse($t->due_date)->isToday()),
            'Tomorrow' => $tasks->filter(fn ($t) => $t->due_date && Carbon::parse($t->due_date)->isTomorrow()),
            'This week' => $tasks->filter(fn ($t) => $t->due_date && Carbon::parse($t->due_date)->gt($today->copy()->addDay()) && Carbon::parse($t->due_date)->lte($endOfWeek)),
            'Next week' => $tasks->filter(fn ($t) => $t->due_date && Carbon::parse($t->due_date)->gt($endOfWeek) && Carbon::parse($t->due_date)->lte($endOfNextWeek)),
            'Later' => $tasks->filter(fn ($t) => $t->due_date && Carbon::parse($t->due_date)->gt($endOfNextWeek)),
            'No due date' => $tasks->filter(fn ($t) => !$t->due_date),
        ];

        $recentlyCompleted = OppTask::where('assignee_id', $user->id)
            ->whereHas('project', function ($q) use ($org) {
                $q->where('organization_id', $org->id);
            })
            ->where('status', 'complete')
            ->where('completed_at', '>=', Carbon::now()->subDays(7))
            ->orderByDesc('completed_at')
            ->with(['project'])
            ->get();

        return view('opportunity.my-tasks', compact('grouped', 'recentlyCompleted', 'user', 'org'));
    }

    /**
     * Inbox: activity log entries relevant to the current user.
     */
    public function inbox()
    {
        $user = auth()->user();
        $org = $user->currentOrganization();

        $activities = OppActivityLog::where(function ($q) use ($user) {
                $q->whereHas('task', function ($tq) use ($user) {
                    $tq->where('assignee_id', $user->id)
                        ->orWhereHas('followers', function ($fq) use ($user) {
                            $fq->where('user_id', $user->id);
                        });
                });
            })
            ->where('user_id', '!=', $user->id)
            ->whereHas('task.project', function ($q) use ($org) {
                $q->where('organization_id', $org->id);
            })
            ->with(['user', 'task.project'])
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('opportunity.inbox', compact('activities', 'user', 'org'));
    }
}
