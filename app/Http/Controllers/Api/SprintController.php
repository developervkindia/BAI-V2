<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Sprint;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    public function index(Project $project)
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $sprints = $project->sprints()
            ->withCount('tasks')
            ->with(['tasks' => fn($q) => $q->select('project_tasks.id', 'is_completed')])
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'name'       => $s->name,
                'start_date' => $s->start_date?->format('Y-m-d'),
                'end_date'   => $s->end_date?->format('Y-m-d'),
                'status'     => $s->status,
                'tasks_count'=> $s->tasks_count,
                'progress'   => $s->progress,
            ]);

        return response()->json($sprints);
    }

    public function store(Request $request, Project $project)
    {
        abort_unless($project->isManager(auth()->user()), 403);

        $data = $request->validate([
            'name'       => 'required|string|max:120',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $sprint = $project->sprints()->create($data);

        return response()->json($sprint, 201);
    }

    public function update(Request $request, Sprint $sprint)
    {
        abort_unless($sprint->project->isManager(auth()->user()), 403);

        $data = $request->validate([
            'name'       => 'sometimes|required|string|max:120',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'status'     => 'sometimes|in:planning,active,completed',
        ]);

        $sprint->update($data);

        return response()->json($sprint);
    }

    public function destroy(Sprint $sprint)
    {
        abort_unless($sprint->project->isManager(auth()->user()), 403);

        $sprint->tasks()->detach();
        $sprint->delete();

        return response()->json(['success' => true]);
    }

    public function addTask(Request $request, Sprint $sprint)
    {
        abort_unless($sprint->project->canEdit(auth()->user()), 403);

        $request->validate(['task_id' => 'required|integer']);

        $task = ProjectTask::where('id', $request->task_id)
            ->where('project_id', $sprint->project_id)
            ->firstOrFail();

        $sprint->tasks()->syncWithoutDetaching([$task->id]);

        return response()->json(['success' => true]);
    }

    public function removeTask(Request $request, Sprint $sprint, ProjectTask $task)
    {
        abort_unless($sprint->project->canEdit(auth()->user()), 403);

        $sprint->tasks()->detach($task->id);

        return response()->json(['success' => true]);
    }

    public function complete(Sprint $sprint)
    {
        abort_unless($sprint->project->isManager(auth()->user()), 403);

        $sprint->update(['status' => 'completed']);

        $completedCount = $sprint->tasks()->where('is_completed', true)->count();
        $totalCount     = $sprint->tasks()->count();

        return response()->json([
            'success'         => true,
            'completed_tasks' => $completedCount,
            'total_tasks'     => $totalCount,
        ]);
    }
}
