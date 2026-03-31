<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Http\Request;

class ProjectBulkActionController extends Controller
{
    public function execute(Request $request, Project $project)
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'integer|exists:project_tasks,id',
            'action' => 'required|string|in:update_status,update_priority,update_assignee,add_label,remove_label,move_list,delete',
            'value' => 'nullable',
        ]);

        $tasks = ProjectTask::whereIn('id', $validated['task_ids'])
            ->where('project_id', $project->id)
            ->get();

        $affected = 0;

        foreach ($tasks as $task) {
            switch ($validated['action']) {
                case 'update_status':
                    $task->update(['project_status_id' => $validated['value']]);
                    $affected++;
                    break;

                case 'update_priority':
                    $task->update(['priority' => $validated['value']]);
                    $affected++;
                    break;

                case 'update_assignee':
                    $task->update(['assignee_id' => $validated['value']]);
                    $affected++;
                    break;

                case 'add_label':
                    if (!$task->labels()->where('project_label_id', $validated['value'])->exists()) {
                        $task->labels()->attach($validated['value']);
                    }
                    $affected++;
                    break;

                case 'remove_label':
                    $task->labels()->detach($validated['value']);
                    $affected++;
                    break;

                case 'move_list':
                    $task->update(['project_task_list_id' => $validated['value']]);
                    $affected++;
                    break;

                case 'delete':
                    $task->delete();
                    $affected++;
                    break;
            }
        }

        return response()->json([
            'message' => "Bulk action completed.",
            'affected' => $affected,
        ]);
    }
}
