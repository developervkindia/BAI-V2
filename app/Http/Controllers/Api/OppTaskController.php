<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OppActivityLog;
use App\Models\OppTask;
use App\Models\OppTaskLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OppTaskController extends Controller
{
    public function myTasks(Request $request): JsonResponse
    {
        $user = auth()->user();
        $org = $user->currentOrganization();

        $tasks = OppTask::where('assignee_id', $user->id)
            ->whereHas('project', fn($q) => $q->where('organization_id', $org->id))
            ->whereNull('parent_task_id')
            ->with(['assignee', 'project', 'section', 'assignees'])
            ->orderByRaw("CASE WHEN status = 'complete' THEN 1 ELSE 0 END")
            ->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('due_date')
            ->orderBy('position')
            ->get()
            ->map(fn($t) => $this->formatTask($t));

        return response()->json(['tasks' => $tasks]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:500',
            'project_id'     => 'required|exists:opp_projects,id',
            'section_id'     => 'nullable|exists:opp_sections,id',
            'assignee_id'    => 'nullable|exists:users,id',
            'due_date'       => 'nullable|date',
            'start_date'     => 'nullable|date',
            'parent_task_id' => 'nullable|exists:opp_tasks,id',
            'description'    => 'nullable|string',
        ]);

        $maxPos = OppTask::where('project_id', $validated['project_id'])
            ->where('section_id', $validated['section_id'] ?? null)
            ->whereNull('parent_task_id')
            ->max('position') ?? 0;

        $task = OppTask::create(array_merge($validated, [
            'created_by' => auth()->id(),
            'status'     => 'incomplete',
            'position'   => $maxPos + 1000,
        ]));

        $this->logActivity($task, 'task.created');

        $task->load(['assignee', 'project', 'section']);
        return response()->json(['task' => $this->formatTask($task)], 201);
    }

    public function show(OppTask $task): JsonResponse
    {
        $task->load([
            'assignee', 'project', 'section',
            'assignees', 'followers', 'tags',
            'comments' => fn($q) => $q->with('user')->latest(),
            'attachments',
            'subtasks' => fn($q) => $q->with('assignee')->orderBy('position'),
            'dependencies.dependsOnTask',
            'customFieldValues.field',
        ]);

        // Load activity for this task
        $activity = OppActivityLog::where('task_id', $task->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $data = $this->formatTask($task);
        $data['activity'] = $activity;

        return response()->json(['task' => $data]);
    }

    public function update(Request $request, OppTask $task): JsonResponse
    {
        $validated = $request->validate([
            'title'        => 'sometimes|required|string|max:500',
            'description'  => 'nullable|string',
            'assignee_id'  => 'nullable|exists:users,id',
            'section_id'   => 'nullable|exists:opp_sections,id',
            'due_date'     => 'nullable|date',
            'start_date'   => 'nullable|date',
            'status'       => 'nullable|string|in:incomplete,complete',
            'is_milestone' => 'nullable|boolean',
        ]);

        // Track changes
        $trackFields = ['title', 'assignee_id', 'due_date', 'start_date', 'status', 'section_id'];
        foreach ($trackFields as $field) {
            if (array_key_exists($field, $validated) && (string)($task->$field ?? '') !== (string)($validated[$field] ?? '')) {
                $this->logActivity($task, "task.{$field}_changed", $field, $task->$field, $validated[$field]);
            }
        }

        // Handle completion
        if (isset($validated['status'])) {
            if ($validated['status'] === 'complete' && $task->status !== 'complete') {
                $validated['completed_at'] = now();
                $validated['completed_by'] = auth()->id();
            } elseif ($validated['status'] === 'incomplete' && $task->status === 'complete') {
                $validated['completed_at'] = null;
                $validated['completed_by'] = null;
            }
        }

        $task->update($validated);
        $task->load(['assignee', 'project', 'section']);

        return response()->json(['task' => $this->formatTask($task)]);
    }

    public function destroy(OppTask $task): JsonResponse
    {
        $task->delete();
        return response()->json(['success' => true]);
    }

    public function complete(OppTask $task): JsonResponse
    {
        if ($task->status === 'complete') {
            $task->update(['status' => 'incomplete', 'completed_at' => null, 'completed_by' => null]);
            $this->logActivity($task, 'task.reopened', 'status', 'complete', 'incomplete');
        } else {
            $task->update(['status' => 'complete', 'completed_at' => now(), 'completed_by' => auth()->id()]);
            $this->logActivity($task, 'task.completed', 'status', 'incomplete', 'complete');
        }

        $task->load(['assignee', 'project', 'section']);
        return response()->json(['task' => $this->formatTask($task)]);
    }

    public function toggleLike(OppTask $task): JsonResponse
    {
        $existing = OppTaskLike::where('task_id', $task->id)->where('user_id', auth()->id())->first();

        if ($existing) {
            $existing->delete();
            $task->decrement('likes_count');
            $liked = false;
        } else {
            OppTaskLike::create(['task_id' => $task->id, 'user_id' => auth()->id()]);
            $task->increment('likes_count');
            $liked = true;
        }

        return response()->json(['liked' => $liked, 'likes_count' => $task->fresh()->likes_count]);
    }

    public function move(Request $request, OppTask $task): JsonResponse
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:opp_sections,id',
            'position'   => 'required|numeric',
        ]);

        $oldSection = $task->section_id;
        $task->update($validated);

        if ($oldSection != $validated['section_id']) {
            $this->logActivity($task, 'task.moved', 'section_id', $oldSection, $validated['section_id']);
        }

        $task->load(['assignee', 'project', 'section']);
        return response()->json(['task' => $this->formatTask($task)]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'            => 'required|array',
            'items.*.id'       => 'required|exists:opp_tasks,id',
            'items.*.position' => 'required|numeric',
        ]);

        foreach ($validated['items'] as $item) {
            OppTask::where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }

    public function duplicate(OppTask $task): JsonResponse
    {
        $newTask = $task->replicate();
        $newTask->title = $task->title . ' (copy)';
        $newTask->status = 'incomplete';
        $newTask->completed_at = null;
        $newTask->completed_by = null;
        $newTask->likes_count = 0;
        $newTask->created_by = auth()->id();
        $newTask->save();

        $newTask->load(['assignee', 'project', 'section']);
        return response()->json(['task' => $this->formatTask($newTask)], 201);
    }

    public function toggleFollower(Request $request, OppTask $task): JsonResponse
    {
        $validated = $request->validate(['user_id' => 'required|exists:users,id']);
        $result = $task->followers()->toggle($validated['user_id']);

        return response()->json([
            'added'     => !empty($result['attached']),
            'followers' => $task->fresh()->followers()->with([])->get()->map(fn($u) => ['id' => $u->id, 'name' => $u->name]),
        ]);
    }

    public function toggleAssignee(Request $request, OppTask $task): JsonResponse
    {
        $validated = $request->validate(['user_id' => 'required|exists:users,id']);
        $result = $task->assignees()->toggle($validated['user_id']);

        return response()->json([
            'added'     => !empty($result['attached']),
            'assignees' => $task->fresh()->assignees()->with([])->get()->map(fn($u) => ['id' => $u->id, 'name' => $u->name]),
        ]);
    }

    // ── Project Members ─────────────────────────────────────────────

    public function projectMembers(\App\Models\OppProject $project): JsonResponse
    {
        $members = $project->members()->get()->map(fn($u) => [
            'id' => $u->id, 'name' => $u->name, 'email' => $u->email,
            'role' => $u->pivot->role,
        ]);
        return response()->json(['members' => $members]);
    }

    public function addProjectMember(Request $request, \App\Models\OppProject $project): JsonResponse
    {
        $validated = $request->validate(['user_id' => 'required|exists:users,id', 'role' => 'nullable|in:editor,commenter,viewer']);
        if ($project->members()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json(['error' => 'Already a member'], 422);
        }
        $project->members()->attach($validated['user_id'], ['role' => $validated['role'] ?? 'editor']);
        $user = \App\Models\User::find($validated['user_id']);
        return response()->json(['success' => true, 'member' => ['id' => $user->id, 'name' => $user->name, 'role' => $validated['role'] ?? 'editor']]);
    }

    public function removeProjectMember(\App\Models\OppProject $project, \App\Models\User $user): JsonResponse
    {
        if ($project->owner_id === $user->id) {
            return response()->json(['error' => 'Cannot remove project owner'], 422);
        }
        $project->members()->detach($user->id);
        return response()->json(['success' => true]);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function logActivity(OppTask $task, string $action, ?string $field = null, $old = null, $new = null): void
    {
        OppActivityLog::create([
            'user_id'    => auth()->id(),
            'task_id'    => $task->id,
            'project_id' => $task->project_id,
            'action'     => $action,
            'field_name' => $field,
            'old_value'  => $old !== null ? (string) $old : null,
            'new_value'  => $new !== null ? (string) $new : null,
            'created_at' => now(),
        ]);
    }

    private function formatTask(OppTask $task): array
    {
        $data = [
            'id'             => $task->id,
            'title'          => $task->title,
            'description'    => $task->description,
            'status'         => $task->status,
            'is_milestone'   => $task->is_milestone,
            'project_id'     => $task->project_id,
            'section_id'     => $task->section_id,
            'parent_task_id' => $task->parent_task_id,
            'assignee_id'    => $task->assignee_id,
            'due_date'       => $task->due_date?->format('Y-m-d'),
            'start_date'     => $task->start_date?->format('Y-m-d'),
            'completed_at'   => $task->completed_at?->toISOString(),
            'likes_count'    => $task->likes_count,
            'position'       => $task->position,
            'created_by'     => $task->created_by,
            'created_at'     => $task->created_at?->toISOString(),
        ];

        if ($task->relationLoaded('assignee')) {
            $data['assignee'] = $task->assignee ? ['id' => $task->assignee->id, 'name' => $task->assignee->name] : null;
        }
        if ($task->relationLoaded('project')) {
            $data['project'] = $task->project ? ['id' => $task->project->id, 'name' => $task->project->name, 'color' => $task->project->color, 'slug' => $task->project->slug] : null;
        }
        if ($task->relationLoaded('section')) {
            $data['section'] = $task->section ? ['id' => $task->section->id, 'name' => $task->section->name] : null;
        }
        if ($task->relationLoaded('assignees')) {
            $data['assignees'] = $task->assignees->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);
        }
        if ($task->relationLoaded('followers')) {
            $data['followers'] = $task->followers->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);
        }
        if ($task->relationLoaded('tags')) {
            $data['tags'] = $task->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color]);
        }
        if ($task->relationLoaded('subtasks')) {
            $data['subtasks'] = $task->subtasks->map(fn($s) => [
                'id' => $s->id, 'title' => $s->title, 'status' => $s->status,
                'assignee' => $s->assignee ? ['id' => $s->assignee->id, 'name' => $s->assignee->name] : null,
            ]);
        }
        if ($task->relationLoaded('comments')) {
            $data['comments'] = $task->comments->map(fn($c) => [
                'id' => $c->id, 'body' => $c->body, 'user' => ['id' => $c->user->id, 'name' => $c->user->name],
                'created_at' => $c->created_at?->toISOString(), 'edited_at' => $c->edited_at?->toISOString(),
            ]);
        }
        if ($task->relationLoaded('attachments')) {
            $data['attachments'] = $task->attachments->map(fn($a) => [
                'id' => $a->id, 'filename' => $a->filename, 'path' => $a->path, 'size' => $a->size, 'mime_type' => $a->mime_type,
            ]);
        }

        return $data;
    }
}
