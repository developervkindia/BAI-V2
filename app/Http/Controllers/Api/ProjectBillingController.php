<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectBillingWeek;
use App\Models\ProjectBillingEntry;
use App\Models\ProjectTimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProjectBillingController extends Controller
{
    /**
     * Create (or reload) a billing week.
     * Auto-sums actual hours from project_time_logs per user for the week.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless($project->canEdit($request->user()), 403);

        $data = $request->validate([
            'week_start' => 'required|date',
            'week_end'   => 'required|date|after_or_equal:week_start',
        ]);

        $weekStart = Carbon::parse($data['week_start'])->startOfDay();
        $weekEnd   = Carbon::parse($data['week_end'])->endOfDay();

        // Get or create the billing week
        $billingWeek = ProjectBillingWeek::firstOrCreate(
            ['project_id' => $project->id, 'week_start' => $weekStart->toDateString()],
            ['week_end' => $weekEnd->toDateString()]
        );

        if ($billingWeek->isLocked()) {
            return response()->json(['success' => false, 'message' => 'This week is locked.'], 422);
        }

        // Gather actual hours per user from time logs
        $taskIds = $project->tasks()->pluck('id');
        $logsByUser = ProjectTimeLog::whereIn('project_task_id', $taskIds)
            ->whereBetween('logged_at', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->select('user_id', DB::raw('SUM(hours) as total_hours'))
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        // Upsert billing entries
        foreach ($logsByUser as $userId => $row) {
            ProjectBillingEntry::updateOrCreate(
                ['billing_week_id' => $billingWeek->id, 'user_id' => $userId],
                ['actual_hours' => $row->total_hours, 'billable_hours' => $row->total_hours]
            );
        }

        $billingWeek->load('entries.user');
        $billingWeek->recalculateTotals();

        // Detailed time logs for the week
        $timeLogs = ProjectTimeLog::whereIn('project_task_id', $taskIds)
            ->whereBetween('logged_at', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->with(['task:id,title', 'user:id,name'])
            ->orderBy('user_id')
            ->orderBy('logged_at')
            ->get();

        return response()->json([
            'success'   => true,
            'week'      => $billingWeek->fresh(['entries.user']),
            'time_logs' => $timeLogs,
        ]);
    }

    public function update(Request $request, ProjectBillingWeek $week)
    {
        abort_unless($week->project->canEdit($request->user()), 403);
        abort_if($week->isLocked(), 422, 'Week is locked.');

        // (No top-level week fields to update right now; reserved for future)
        return response()->json(['success' => true]);
    }

    public function lock(Request $request, ProjectBillingWeek $week)
    {
        abort_unless($week->project->canEdit($request->user()), 403);
        abort_if($week->isLocked(), 422, 'Already locked.');

        $week->load('entries');
        $week->recalculateTotals();
        $week->locked_by = $request->user()->id;
        $week->locked_at = now();
        $week->save();

        return response()->json(['success' => true, 'week' => $week->fresh(['entries.user', 'locker'])]);
    }

    public function logs(Request $request, ProjectBillingWeek $week)
    {
        abort_unless($week->project->canEdit($request->user()), 403);

        $taskIds = $week->project->tasks()->pluck('id');
        $timeLogs = ProjectTimeLog::whereIn('project_task_id', $taskIds)
            ->whereBetween('logged_at', [$week->week_start, $week->week_end])
            ->with(['task:id,title', 'user:id,name'])
            ->orderBy('user_id')
            ->orderBy('logged_at')
            ->get();

        return response()->json([
            'week'      => $week->load(['entries.user', 'locker']),
            'time_logs' => $timeLogs,
        ]);
    }

    public function updateEntry(Request $request, ProjectBillingEntry $entry)
    {
        abort_unless($entry->billingWeek->project->canEdit($request->user()), 403);
        abort_if($entry->billingWeek->isLocked(), 422, 'Week is locked.');

        $data = $request->validate([
            'billable_hours' => 'required|numeric|min:0|max:999',
            'notes'          => 'nullable|string|max:500',
        ]);

        $entry->update($data);
        $entry->billingWeek->load('entries');
        $entry->billingWeek->recalculateTotals();

        return response()->json(['success' => true, 'entry' => $entry->fresh(), 'week' => $entry->billingWeek->fresh()]);
    }
}
