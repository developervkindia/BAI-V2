<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProjectReportService
{
    public function taskCompletionOverTime(Project $project, Carbon $from, Carbon $to): Collection
    {
        $created = DB::table('project_tasks')
            ->where('project_id', $project->id)
            ->whereNull('parent_task_id')->whereNull('deleted_at')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $completed = DB::table('project_tasks')
            ->where('project_id', $project->id)
            ->whereNull('parent_task_id')->whereNull('deleted_at')
            ->whereBetween('completed_at', [$from, $to])
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
            ->groupBy(DB::raw('DATE(completed_at)'))
            ->orderBy('date')
            ->get();

        return collect(['created' => $created, 'completed' => $completed]);
    }

    public function tasksByAssignee(Project $project): Collection
    {
        return DB::table('project_tasks')
            ->leftJoin('users', 'project_tasks.assignee_id', '=', 'users.id')
            ->where('project_tasks.project_id', $project->id)
            ->whereNull('project_tasks.parent_task_id')
            ->whereNull('project_tasks.deleted_at')
            ->select(
                'users.id', 'users.name',
                DB::raw('SUM(CASE WHEN project_tasks.is_completed = 1 THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN project_tasks.is_completed = 0 THEN 1 ELSE 0 END) as open'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('users.id', 'users.name')
            ->get();
    }

    public function tasksByMilestone(Project $project): Collection
    {
        return DB::table('project_tasks')
            ->leftJoin('milestones', 'project_tasks.milestone_id', '=', 'milestones.id')
            ->where('project_tasks.project_id', $project->id)
            ->whereNull('project_tasks.parent_task_id')
            ->whereNull('project_tasks.deleted_at')
            ->select(
                'milestones.id', 'milestones.name',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN project_tasks.is_completed = 1 THEN 1 ELSE 0 END) as completed')
            )
            ->groupBy('milestones.id', 'milestones.name')
            ->get();
    }

    public function hoursByUser(Project $project, Carbon $from, Carbon $to): Collection
    {
        $taskIds = $project->tasks()->pluck('id');

        return DB::table('project_time_logs')
            ->join('users', 'project_time_logs.user_id', '=', 'users.id')
            ->whereIn('project_time_logs.project_task_id', $taskIds)
            ->whereBetween('project_time_logs.logged_at', [$from->toDateString(), $to->toDateString()])
            ->select('users.id', 'users.name', DB::raw('SUM(hours) as total_hours'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_hours')
            ->get();
    }

    public function hoursByDate(Project $project, Carbon $from, Carbon $to): Collection
    {
        $taskIds = $project->tasks()->pluck('id');

        return DB::table('project_time_logs')
            ->whereIn('project_task_id', $taskIds)
            ->whereBetween('logged_at', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('logged_at as date, SUM(hours) as total_hours')
            ->groupBy('logged_at')
            ->orderBy('logged_at')
            ->get();
    }

    public function hoursByTask(Project $project, Carbon $from, Carbon $to): Collection
    {
        $taskIds = $project->tasks()->pluck('id');

        return DB::table('project_time_logs')
            ->join('project_tasks', 'project_time_logs.project_task_id', '=', 'project_tasks.id')
            ->whereIn('project_time_logs.project_task_id', $taskIds)
            ->whereBetween('project_time_logs.logged_at', [$from->toDateString(), $to->toDateString()])
            ->select(
                'project_tasks.id', 'project_tasks.title', 'project_tasks.estimated_hours',
                DB::raw('SUM(project_time_logs.hours) as total_hours')
            )
            ->groupBy('project_tasks.id', 'project_tasks.title', 'project_tasks.estimated_hours')
            ->orderByDesc('total_hours')
            ->limit(20)
            ->get();
    }

    public function milestoneProgress(Project $project): Collection
    {
        return DB::table('milestones')
            ->leftJoin('project_tasks', function ($j) {
                $j->on('project_tasks.milestone_id', '=', 'milestones.id')
                  ->whereNull('project_tasks.parent_task_id')
                  ->whereNull('project_tasks.deleted_at');
            })
            ->where('milestones.project_id', $project->id)
            ->select(
                'milestones.id', 'milestones.name', 'milestones.due_date', 'milestones.status',
                DB::raw('COUNT(project_tasks.id) as total_tasks'),
                DB::raw('SUM(CASE WHEN project_tasks.is_completed = 1 THEN 1 ELSE 0 END) as completed_tasks')
            )
            ->groupBy('milestones.id', 'milestones.name', 'milestones.due_date', 'milestones.status')
            ->orderBy('milestones.due_date')
            ->get()
            ->map(function ($m) {
                $m->progress = $m->total_tasks > 0 ? round(($m->completed_tasks / $m->total_tasks) * 100) : 0;
                return $m;
            });
    }

    public function sprintBurndown(Sprint $sprint): array
    {
        $totalPoints = DB::table('project_tasks')
            ->join('project_sprint_tasks', 'project_tasks.id', '=', 'project_sprint_tasks.project_task_id')
            ->where('project_sprint_tasks.sprint_id', $sprint->id)
            ->whereNull('project_tasks.deleted_at')
            ->selectRaw('COALESCE(SUM(story_points), COUNT(*)) as total')
            ->value('total');

        $dailyCompletion = DB::table('project_tasks')
            ->join('project_sprint_tasks', 'project_tasks.id', '=', 'project_sprint_tasks.project_task_id')
            ->where('project_sprint_tasks.sprint_id', $sprint->id)
            ->whereNull('project_tasks.deleted_at')
            ->where('project_tasks.is_completed', true)
            ->selectRaw('DATE(project_tasks.completed_at) as date, COALESCE(SUM(story_points), COUNT(*)) as points')
            ->groupBy(DB::raw('DATE(project_tasks.completed_at)'))
            ->orderBy('date')
            ->get();

        return [
            'total'            => $totalPoints,
            'daily_completion' => $dailyCompletion,
            'start_date'       => $sprint->start_date?->format('Y-m-d'),
            'end_date'         => $sprint->end_date?->format('Y-m-d'),
        ];
    }
}
