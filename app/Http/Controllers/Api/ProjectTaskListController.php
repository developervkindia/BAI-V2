<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TaskList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTaskListController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate(['name' => 'required|string|max:255']);

        $maxPos = $project->taskLists()->max('position') ?? 0;

        $taskList = $project->taskLists()->create([
            'name'     => $validated['name'],
            'position' => $maxPos + 1000,
        ]);

        return response()->json(['success' => true, 'task_list' => $taskList]);
    }

    public function update(Request $request, TaskList $taskList): JsonResponse
    {
        abort_unless($taskList->project->canEdit(auth()->user()), 403);

        $validated = $request->validate(['name' => 'required|string|max:255']);

        $taskList->update($validated);

        return response()->json(['success' => true]);
    }

    public function destroy(TaskList $taskList): JsonResponse
    {
        abort_unless($taskList->project->canEdit(auth()->user()), 403);

        $taskList->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'items'          => 'required|array',
            'items.*.id'     => 'required|integer',
            'items.*.position' => 'required|numeric',
        ]);

        foreach ($validated['items'] as $item) {
            TaskList::where('id', $item['id'])
                ->where('project_id', $project->id)
                ->update(['position' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }
}
