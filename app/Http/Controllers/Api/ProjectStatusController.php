<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectStatusController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $statuses = $project->statuses()->orderBy('position')->get()
            ->map(fn(ProjectStatus $s) => $this->format($s));

        return response()->json(['statuses' => $statuses]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->isManager(auth()->user()), 403);

        $validated = $request->validate([
            'name'               => 'required|string|max:50',
            'color'              => 'required|string|max:20',
            'is_completed_state' => 'sometimes|boolean',
        ]);

        $maxPos = $project->statuses()->max('position') ?? 0;

        $status = $project->statuses()->create([
            'name'               => $validated['name'],
            'slug'               => Str::slug($validated['name'], '_'),
            'color'              => $validated['color'],
            'position'           => $maxPos + 1,
            'is_completed_state' => $validated['is_completed_state'] ?? false,
            'is_default'         => false,
        ]);

        return response()->json(['success' => true, 'status' => $this->format($status)]);
    }

    public function update(Request $request, ProjectStatus $status): JsonResponse
    {
        abort_unless($status->project->isManager(auth()->user()), 403);

        $validated = $request->validate([
            'name'               => 'sometimes|string|max:50',
            'color'              => 'sometimes|string|max:20',
            'is_completed_state' => 'sometimes|boolean',
            'is_default'         => 'sometimes|boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name'], '_');
        }

        // If setting as default, unset other defaults first
        if (!empty($validated['is_default'])) {
            $status->project->statuses()->where('id', '!=', $status->id)->update(['is_default' => false]);
        }

        $status->update($validated);

        return response()->json(['success' => true, 'status' => $this->format($status)]);
    }

    public function destroy(Request $request, ProjectStatus $status): JsonResponse
    {
        abort_unless($status->project->isManager(auth()->user()), 403);

        // Cannot delete the last status
        if ($status->project->statuses()->count() <= 1) {
            return response()->json(['error' => 'Cannot delete the only remaining status.'], 422);
        }

        // Reassign tasks to another status
        $reassignTo = $request->input('reassign_to');
        if ($status->tasks()->exists()) {
            if (!$reassignTo) {
                // Default: reassign to the default status, or first available
                $fallback = $status->project->statuses()
                    ->where('id', '!=', $status->id)
                    ->orderByDesc('is_default')
                    ->orderBy('position')
                    ->first();
                $reassignTo = $fallback?->id;
            }

            if ($reassignTo) {
                $status->tasks()->update([
                    'project_status_id' => $reassignTo,
                    'status'            => ProjectStatus::find($reassignTo)?->slug ?? 'open',
                ]);
            }
        }

        $status->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->isManager(auth()->user()), 403);

        $validated = $request->validate([
            'items'              => 'required|array',
            'items.*.id'         => 'required|integer',
            'items.*.position'   => 'required|numeric',
        ]);

        foreach ($validated['items'] as $item) {
            ProjectStatus::where('id', $item['id'])
                ->where('project_id', $project->id)
                ->update(['position' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }

    private function format(ProjectStatus $status): array
    {
        return [
            'id'                 => $status->id,
            'name'               => $status->name,
            'slug'               => $status->slug,
            'color'              => $status->color,
            'position'           => $status->position,
            'is_completed_state' => $status->is_completed_state,
            'is_default'         => $status->is_default,
            'task_count'         => $status->tasks_count ?? $status->tasks()->count(),
        ];
    }
}
