<?php

namespace App\Http\Controllers\Opportunity;

use App\Http\Controllers\Controller;
use App\Models\OppProject;
use App\Models\OppTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OppReportController extends Controller
{
    public function index()
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();

        return view('opportunity.reports.index', compact('org'));
    }

    public function taskCompletion(Request $request): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();

        $data = OppTask::whereHas('project', fn ($q) => $q->where('organization_id', $org->id))
            ->whereNotNull('completed_at')
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function teamWorkload(Request $request): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();

        $data = OppTask::whereHas('project', fn ($q) => $q->where('organization_id', $org->id))
            ->whereNotNull('assignee_id')
            ->selectRaw('assignee_id, status, COUNT(*) as count')
            ->groupBy('assignee_id', 'status')
            ->with('assignee')
            ->get()
            ->groupBy('assignee_id')
            ->map(function ($tasks) {
                $assignee = $tasks->first()->assignee;
                return [
                    'assignee' => $assignee,
                    'statuses' => $tasks->pluck('count', 'status'),
                ];
            })
            ->values();

        $workload = $data->map(function ($item) {
            return [
                'name' => $item['assignee']?->name ?? 'Unassigned',
                'incomplete' => $item['statuses']['incomplete'] ?? 0,
                'complete' => $item['statuses']['complete'] ?? 0,
            ];
        });

        return response()->json(['workload' => $workload]);
    }

    public function projectProgress(Request $request): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();

        $data = OppProject::where('organization_id', $org->id)
            ->where('is_template', false)
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($q) => $q->where('status', 'complete'),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn ($project) => [
                'id'               => $project->id,
                'name'             => $project->name,
                'total_tasks'      => $project->tasks_count,
                'completed_tasks'  => $project->completed_tasks_count,
                'progress_percent' => $project->tasks_count > 0
                    ? round(($project->completed_tasks_count / $project->tasks_count) * 100, 1)
                    : 0,
            ]);

        $projects = $data->map(fn($p) => [
            'id' => $p['id'], 'name' => $p['name'], 'color' => '#14B8A6',
            'progress' => $p['progress_percent'],
        ]);

        return response()->json(['projects' => $projects]);
    }
}
