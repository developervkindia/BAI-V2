<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTimeLog;
use App\Models\UserCapacity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    // ── Capacity CRUD ────────────────────────────────────────────────

    public function capacities(Project $project): JsonResponse
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $caps = UserCapacity::where('project_id', $project->id)
            ->with('user:id,name')
            ->get()
            ->map(fn($c) => [
                'id'    => $c->id,
                'user'  => $c->user ? ['id' => $c->user->id, 'name' => $c->user->name] : null,
                'weekly_capacity_hours' => $c->weekly_capacity_hours,
                'effective_from'  => $c->effective_from?->format('Y-m-d'),
                'effective_until' => $c->effective_until?->format('Y-m-d'),
            ]);

        return response()->json(['capacities' => $caps]);
    }

    public function storeCapacity(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->isManager(auth()->user()), 403);

        $data = $request->validate([
            'user_id'               => 'required|exists:users,id',
            'weekly_capacity_hours' => 'required|numeric|min:0|max:168',
            'effective_from'        => 'nullable|date',
            'effective_until'       => 'nullable|date|after_or_equal:effective_from',
        ]);

        $cap = UserCapacity::updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $data['user_id'], 'effective_from' => $data['effective_from'] ?? null],
            $data
        );

        return response()->json(['success' => true, 'capacity' => $cap->load('user:id,name')]);
    }

    public function updateCapacity(Request $request, UserCapacity $capacity): JsonResponse
    {
        abort_unless($capacity->project->isManager(auth()->user()), 403);

        $data = $request->validate([
            'weekly_capacity_hours' => 'sometimes|numeric|min:0|max:168',
            'effective_from'        => 'nullable|date',
            'effective_until'       => 'nullable|date',
        ]);

        $capacity->update($data);

        return response()->json(['success' => true, 'capacity' => $capacity->fresh('user:id,name')]);
    }

    // ── Workload data ────────────────────────────────────────────────

    public function workload(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $weekStart = Carbon::parse($request->input('week_start', now()->startOfWeek()))->toDateString();
        $weekEnd   = Carbon::parse($weekStart)->endOfWeek()->toDateString();

        $members = $project->members()->get();
        $taskIds = $project->tasks()->pluck('id');

        $data = $members->map(function ($member) use ($project, $taskIds, $weekStart, $weekEnd) {
            $capacity = UserCapacity::getForUser($member->id, $project->id);
            $capHours = $capacity?->weekly_capacity_hours ?? 40;

            $allocatedHours = $project->tasks()
                ->where('assignee_id', $member->id)
                ->whereNull('parent_task_id')
                ->whereNull('deleted_at')
                ->where('is_completed', false)
                ->sum('estimated_hours');

            $loggedHours = ProjectTimeLog::whereIn('project_task_id', $taskIds)
                ->where('user_id', $member->id)
                ->whereBetween('logged_at', [$weekStart, $weekEnd])
                ->sum('hours');

            // Daily breakdown
            $dailyLogs = ProjectTimeLog::whereIn('project_task_id', $taskIds)
                ->where('user_id', $member->id)
                ->whereBetween('logged_at', [$weekStart, $weekEnd])
                ->selectRaw('logged_at, SUM(hours) as day_hours')
                ->groupBy('logged_at')
                ->pluck('day_hours', 'logged_at');

            return [
                'user'            => ['id' => $member->id, 'name' => $member->name],
                'role'            => $member->pivot->role,
                'capacity'        => $capHours,
                'allocated_hours' => round($allocatedHours, 1),
                'logged_hours'    => round($loggedHours, 2),
                'utilization_pct' => $capHours > 0 ? round(($loggedHours / $capHours) * 100, 1) : 0,
                'daily_logs'      => $dailyLogs,
                'open_tasks'      => $project->tasks()
                    ->where('assignee_id', $member->id)
                    ->whereNull('parent_task_id')
                    ->whereNull('deleted_at')
                    ->where('is_completed', false)
                    ->count(),
            ];
        });

        return response()->json(['workload' => $data, 'week_start' => $weekStart, 'week_end' => $weekEnd]);
    }

    // ── Budget forecast ──────────────────────────────────────────────

    public function budgetForecast(Project $project): JsonResponse
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $taskIds = $project->tasks()->pluck('id');
        $rate = (float) ($project->hourly_rate ?? 0);
        $budget = (float) ($project->budget ?? 0);

        $totalLogged = ProjectTimeLog::whereIn('project_task_id', $taskIds)->sum('hours');
        $actualSpend = $totalLogged * $rate;
        $remaining = $budget - $actualSpend;

        $recentHours = ProjectTimeLog::whereIn('project_task_id', $taskIds)
            ->where('logged_at', '>=', now()->subWeeks(4))
            ->sum('hours');
        $weeklyBurnRate = $recentHours / 4;
        $weeklyBurnCost = $weeklyBurnRate * $rate;

        $remainingEstimated = $project->tasks()
            ->whereNull('parent_task_id')->whereNull('deleted_at')
            ->where('is_completed', false)
            ->sum('estimated_hours');

        $weeksRemaining = $weeklyBurnCost > 0 ? round($remaining / $weeklyBurnCost, 1) : null;
        $completionWeeks = $weeklyBurnRate > 0 ? round($remainingEstimated / $weeklyBurnRate, 1) : null;
        $estimatedTotalCost = $actualSpend + ($remainingEstimated * $rate);

        return response()->json([
            'weekly_burn_rate'    => round($weeklyBurnRate, 1),
            'weekly_burn_cost'    => round($weeklyBurnCost, 2),
            'remaining_budget'    => round($remaining, 2),
            'weeks_until_exhausted' => $weeksRemaining,
            'estimated_completion_weeks' => $completionWeeks,
            'estimated_total_cost' => round($estimatedTotalCost, 2),
            'budget_variance'     => round($budget - $estimatedTotalCost, 2),
        ]);
    }
}
