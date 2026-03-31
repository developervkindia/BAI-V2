<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\ProjectTaskList;
use Illuminate\Http\Request;

class ProjectRecycleBinController extends Controller
{
    public function index(Project $project)
    {
        abort_unless($project->isManager(auth()->user()), 403);

        $tasks = $project->tasks()->onlyTrashed()->latest('deleted_at')->get();
        $taskLists = $project->taskLists()->onlyTrashed()->latest('deleted_at')->get();
        $milestones = $project->milestones()->onlyTrashed()->latest('deleted_at')->get();

        return response()->json([
            'tasks' => $tasks,
            'task_lists' => $taskLists,
            'milestones' => $milestones,
        ]);
    }

    public function restore(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:task,list,milestone',
            'id' => 'required|integer',
        ]);

        $model = $this->resolveModel($validated['type'], $validated['id']);

        abort_unless($model, 404);
        abort_unless($model->project->isManager(auth()->user()), 403);

        $model->restore();

        return response()->json(['message' => ucfirst($validated['type']) . ' restored.']);
    }

    public function permanentDelete(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:task,list,milestone',
            'id' => 'required|integer',
        ]);

        $model = $this->resolveModel($validated['type'], $validated['id']);

        abort_unless($model, 404);
        abort_unless($model->project->isManager(auth()->user()), 403);

        $model->forceDelete();

        return response()->json(['message' => ucfirst($validated['type']) . ' permanently deleted.']);
    }

    public function emptyBin(Project $project)
    {
        abort_unless($project->isManager(auth()->user()), 403);

        $project->tasks()->onlyTrashed()->forceDelete();
        $project->taskLists()->onlyTrashed()->forceDelete();
        $project->milestones()->onlyTrashed()->forceDelete();

        return response()->json(['message' => 'Recycle bin emptied.']);
    }

    protected function resolveModel(string $type, int $id)
    {
        return match ($type) {
            'task' => ProjectTask::onlyTrashed()->find($id),
            'list' => ProjectTaskList::onlyTrashed()->find($id),
            'milestone' => ProjectMilestone::onlyTrashed()->find($id),
            default => null,
        };
    }
}
