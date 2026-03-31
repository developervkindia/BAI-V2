<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectStatus;
use App\Models\ProjectTask;
use App\Models\TaskList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectTaskController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'title'             => 'required|string|max:500',
            'task_list_id'      => 'required|exists:task_lists,id',
            'assignee_id'       => 'nullable|exists:users,id',
            'priority'          => 'in:none,low,medium,high,critical',
            'due_date'          => 'nullable|date',
            'parent_task_id'    => 'nullable|exists:project_tasks,id',
            'issue_type'        => 'nullable|in:task,bug,story,epic',
            'story_points'      => 'nullable|integer|min:0',
            'project_status_id' => 'nullable|exists:project_statuses,id',
        ]);

        // Resolve status: use provided project_status_id, or fall back to project's default status
        if (empty($validated['project_status_id'])) {
            $defaultStatus = $project->statuses()->where('is_default', true)->first()
                          ?? $project->statuses()->orderBy('position')->first();
            $validated['project_status_id'] = $defaultStatus?->id;
            $validated['status'] = $defaultStatus?->slug ?? 'open';
        } else {
            $statusObj = ProjectStatus::find($validated['project_status_id']);
            $validated['status'] = $statusObj?->slug ?? 'open';
        }

        $maxPos = ProjectTask::where('task_list_id', $validated['task_list_id'])
            ->whereNull('parent_task_id')
            ->max('position') ?? 0;

        $task = $project->tasks()->create(array_merge($validated, [
            'position'   => $maxPos + 1000,
            'issue_type' => $validated['issue_type'] ?? 'task',
        ]));

        ProjectActivity::create([
            'project_task_id' => $task->id,
            'user_id'         => auth()->id(),
            'type'            => 'created',
            'field_name'      => null,
            'old_value'       => null,
            'new_value'       => $task->title,
        ]);

        $task->load(['assignee', 'subtasks', 'labels', 'projectStatus']);

        return response()->json(['success' => true, 'task' => $this->formatTask($task)]);
    }

    public function show(ProjectTask $task): JsonResponse
    {
        abort_unless($task->project->canAccess(auth()->user()), 403);

        $task->load([
            'assignee', 'taskList', 'milestone', 'projectStatus', 'subtasks.assignee', 'subtasks.projectStatus',
            'comments.user', 'members', 'labels', 'watchers',
            'timeLogs', 'attachments',
            'links.linkedTask',
            'customFieldValues.field',
        ]);

        return response()->json([
            'task' => [
                'id'                => $task->id,
                'title'             => $task->title,
                'description'       => $task->description,
                'status'            => $task->status,
                'project_status_id' => $task->project_status_id,
                'status_name'       => $task->status_name,
                'status_color'      => $task->status_color,
                'priority'          => $task->priority,
                'issue_type'       => $task->issue_type ?? 'task',
                'story_points'     => $task->story_points,
                'assignee_id'      => $task->assignee_id,
                'assignee'         => $task->assignee ? ['id' => $task->assignee->id, 'name' => $task->assignee->name] : null,
                'milestone_id'     => $task->milestone_id,
                'milestone'        => $task->milestone ? ['id' => $task->milestone->id, 'name' => $task->milestone->name] : null,
                'task_list_id'     => $task->task_list_id,
                'task_list'        => ['id' => $task->taskList->id, 'name' => $task->taskList->name],
                'start_date'       => $task->start_date?->format('Y-m-d'),
                'due_date'         => $task->due_date?->format('Y-m-d'),
                'due_date_status'  => $task->due_date_status,
                'estimated_hours'  => $task->estimated_hours,
                'actual_hours'     => $task->actual_hours,
                'is_completed'     => $task->is_completed,
                'labels'           => $task->labels->map(fn($l) => ['id' => $l->id, 'name' => $l->name, 'color' => $l->color])->values(),
                'attachments'      => $task->attachments->map(fn($a) => [
                    'id'        => $a->id,
                    'filename'  => $a->filename,
                    'url'       => Storage::disk('public')->url($a->path),
                    'size_fmt'  => $this->formatBytes($a->size),
                    'mime_type' => $a->mime_type,
                    'is_mine'   => $a->user_id === auth()->id(),
                ])->values(),
                'time_logged'      => round($task->timeLogs->sum('hours'), 2),
                'watchers'         => $task->watchers->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values(),
                'is_watching'      => $task->watchers->contains(auth()->id()),
                'links'            => $task->links->map(fn($l) => [
                    'id'         => $l->id,
                    'type'       => $l->type,
                    'type_label' => $this->linkTypeLabel($l->type),
                    'other_task' => $l->linkedTask ? [
                        'id'       => $l->linkedTask->id,
                        'title'    => $l->linkedTask->title,
                        'status'   => $l->linkedTask->status,
                        'priority' => $l->linkedTask->priority,
                    ] : null,
                ])->values(),
                'subtasks'         => $task->subtasks->map(fn($s) => $this->formatTask($s))->values(),
                'comments'         => $task->comments->map(fn($c) => [
                    'id'         => $c->id,
                    'content'    => $c->content,
                    'user'       => ['id' => $c->user->id, 'name' => $c->user->name],
                    'created_at' => $c->created_at->diffForHumans(),
                    'is_mine'    => $c->user_id === auth()->id(),
                ])->values(),
                'custom_field_values' => $task->customFieldValues->map(fn($v) => [
                    'field_id'   => $v->custom_field_id,
                    'field_name' => $v->field?->name,
                    'field_type' => $v->field?->type,
                    'value'      => $v->value,
                ])->values(),
            ],
        ]);
    }

    public function update(Request $request, ProjectTask $task): JsonResponse
    {
        abort_unless($task->project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'title'             => 'sometimes|required|string|max:500',
            'description'       => 'nullable|string',
            'project_status_id' => 'sometimes|exists:project_statuses,id',
            'priority'          => 'sometimes|in:none,low,medium,high,critical',
            'assignee_id'       => 'nullable|exists:users,id',
            'milestone_id'      => 'nullable|exists:milestones,id',
            'start_date'        => 'nullable|date',
            'due_date'          => 'nullable|date',
            'estimated_hours'   => 'nullable|numeric|min:0',
            'actual_hours'      => 'nullable|numeric|min:0',
            'is_completed'      => 'sometimes|boolean',
            'issue_type'        => 'sometimes|in:task,bug,story,epic',
            'story_points'      => 'nullable|integer|min:0',
        ]);

        // Handle project_status_id change — sync is_completed and status slug
        if (isset($validated['project_status_id'])) {
            $newStatus = ProjectStatus::find($validated['project_status_id']);
            if ($newStatus) {
                $validated['status'] = $newStatus->slug;
                if ($newStatus->is_completed_state) {
                    $validated['is_completed'] = true;
                    $validated['completed_at'] = $task->completed_at ?? now();
                } elseif ($task->is_completed) {
                    $validated['is_completed'] = false;
                    $validated['completed_at'] = null;
                }
            }
        }

        // Handle direct is_completed toggle (e.g. checkbox)
        if (isset($validated['is_completed']) && !isset($validated['project_status_id'])) {
            $validated['completed_at'] = $validated['is_completed'] ? now() : null;
            if ($validated['is_completed']) {
                // Find a completed status
                $completedStatus = $task->project->statuses()->where('is_completed_state', true)->first();
                if ($completedStatus) {
                    $validated['project_status_id'] = $completedStatus->id;
                    $validated['status'] = $completedStatus->slug;
                }
            } else {
                // Revert to default status
                $defaultStatus = $task->project->statuses()->where('is_default', true)->first();
                if ($defaultStatus) {
                    $validated['project_status_id'] = $defaultStatus->id;
                    $validated['status'] = $defaultStatus->slug;
                }
            }
        }

        // Track changes for activity log
        $trackFields = ['project_status_id', 'status', 'priority', 'assignee_id', 'milestone_id', 'due_date', 'title', 'is_completed', 'issue_type'];
        $originals   = [];
        foreach ($trackFields as $field) {
            if (array_key_exists($field, $validated)) {
                $originals[$field] = $task->$field instanceof \Illuminate\Support\Carbon
                    ? $task->$field->format('Y-m-d')
                    : $task->$field;
            }
        }

        $task->update($validated);

        // Log activity for changed fields
        foreach ($originals as $field => $oldValue) {
            $newRaw  = $task->$field;
            $newValue = $newRaw instanceof \Illuminate\Support\Carbon ? $newRaw->format('Y-m-d') : $newRaw;
            // Normalize booleans
            $oldNorm = is_bool($oldValue) ? (int) $oldValue : $oldValue;
            $newNorm = is_bool($newValue) ? (int) $newValue : $newValue;
            if ((string) $oldNorm !== (string) $newNorm) {
                ProjectActivity::create([
                    'project_task_id' => $task->id,
                    'user_id'         => auth()->id(),
                    'type'            => 'field_changed',
                    'field_name'      => $field,
                    'old_value'       => $oldNorm,
                    'new_value'       => $newNorm,
                ]);
            }
        }

        $task->load(['assignee', 'subtasks', 'labels', 'projectStatus']);

        return response()->json(['success' => true, 'task' => $this->formatTask($task)]);
    }

    public function destroy(ProjectTask $task): JsonResponse
    {
        abort_unless($task->project->canEdit(auth()->user()), 403);

        $task->delete();

        return response()->json(['success' => true]);
    }

    public function move(Request $request, ProjectTask $task): JsonResponse
    {
        abort_unless($task->project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'task_list_id' => 'required|exists:task_lists,id',
            'position'     => 'required|numeric',
        ]);

        $task->update($validated);

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'items'            => 'required|array',
            'items.*.id'       => 'required|integer',
            'items.*.position' => 'required|numeric',
        ]);

        foreach ($validated['items'] as $item) {
            ProjectTask::where('id', $item['id'])
                ->where('project_id', $project->id)
                ->update(['position' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleWatch(ProjectTask $task): JsonResponse
    {
        abort_unless($task->project->canAccess(auth()->user()), 403);

        $user = auth()->user();
        if ($task->watchers()->where('user_id', $user->id)->exists()) {
            $task->watchers()->detach($user->id);
            $watching = false;
        } else {
            $task->watchers()->attach($user->id);
            $watching = true;
        }

        return response()->json(['watching' => $watching]);
    }

    private function formatTask(ProjectTask $task): array
    {
        return [
            'id'                => $task->id,
            'title'             => $task->title,
            'status'            => $task->status,
            'project_status_id' => $task->project_status_id,
            'status_name'       => $task->status_name,
            'status_color'      => $task->status_color,
            'priority'          => $task->priority,
            'issue_type'        => $task->issue_type ?? 'task',
            'story_points'      => $task->story_points,
            'task_list_id'      => $task->task_list_id,
            'assignee_id'       => $task->assignee_id,
            'assignee'          => $task->assignee ? ['id' => $task->assignee->id, 'name' => $task->assignee->name] : null,
            'due_date'          => $task->due_date?->format('Y-m-d'),
            'due_date_fmt'      => $task->due_date?->format('M j'),
            'due_date_status'   => $task->due_date_status,
            'is_completed'      => $task->is_completed,
            'position'          => $task->position,
            'labels'            => $task->labels ? $task->labels->map(fn($l) => ['id' => $l->id, 'name' => $l->name, 'color' => $l->color])->values() : [],
            'subtask_count'     => $task->subtasks?->count() ?? 0,
            'comments_count'    => $task->comments_count ?? 0,
            'parent_task_id'    => $task->parent_task_id,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    private function linkTypeLabel(string $type): string
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
