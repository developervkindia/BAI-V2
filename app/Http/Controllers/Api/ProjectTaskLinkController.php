<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectTask;
use App\Models\ProjectTaskLink;
use Illuminate\Http\Request;

class ProjectTaskLinkController extends Controller
{
    public function index(ProjectTask $task)
    {
        abort_unless($task->project->canAccess(auth()->user()), 403);

        $links = ProjectTaskLink::where('task_id', $task->id)
            ->orWhere('linked_task_id', $task->id)
            ->with('task', 'linkedTask')
            ->get()
            ->map(function ($link) use ($task) {
                $other      = $link->task_id === $task->id ? $link->linkedTask : $link->task;
                $type       = $link->task_id === $task->id ? $link->type : $this->reverseType($link->type);
                return [
                    'id'         => $link->id,
                    'type'       => $type,
                    'type_label' => $this->typeLabel($type),
                    'other_task' => [
                        'id'       => $other->id,
                        'title'    => $other->title,
                        'status'   => $other->status,
                        'priority' => $other->priority,
                    ],
                ];
            });

        return response()->json($links);
    }

    public function store(Request $request, ProjectTask $task)
    {
        abort_unless($task->project->canEdit(auth()->user()), 403);

        $data = $request->validate([
            'linked_task_id' => 'required|integer|different:' . $task->id,
            'type'           => 'required|in:relates_to,blocks,blocked_by,duplicates',
        ]);

        $linkedTask = ProjectTask::where('id', $data['linked_task_id'])
            ->where('project_id', $task->project_id)
            ->firstOrFail();

        // Prevent duplicate
        $exists = ProjectTaskLink::where(function ($q) use ($task, $linkedTask) {
            $q->where('task_id', $task->id)->where('linked_task_id', $linkedTask->id);
        })->orWhere(function ($q) use ($task, $linkedTask) {
            $q->where('task_id', $linkedTask->id)->where('linked_task_id', $task->id);
        })->exists();

        if ($exists) {
            return response()->json(['error' => 'Link already exists.'], 422);
        }

        $link = ProjectTaskLink::create([
            'task_id'        => $task->id,
            'linked_task_id' => $linkedTask->id,
            'type'           => $data['type'],
        ]);

        return response()->json([
            'id'         => $link->id,
            'type'       => $link->type,
            'type_label' => $this->typeLabel($link->type),
            'other_task' => [
                'id'       => $linkedTask->id,
                'title'    => $linkedTask->title,
                'status'   => $linkedTask->status,
                'priority' => $linkedTask->priority,
            ],
        ], 201);
    }

    public function destroy(ProjectTaskLink $link)
    {
        $task = $link->task;
        abort_unless($task->project->canEdit(auth()->user()), 403);

        $link->delete();

        return response()->json(['success' => true]);
    }

    private function reverseType(string $type): string
    {
        return match ($type) {
            'blocks'     => 'blocked_by',
            'blocked_by' => 'blocks',
            default      => $type,
        };
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'relates_to' => 'Relates to',
            'blocks'     => 'Blocks',
            'blocked_by' => 'Blocked by',
            'duplicates' => 'Duplicates',
            default      => $type,
        };
    }
}
