<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectSavedView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectSavedViewController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $views = ProjectSavedView::where('project_id', $project->id)
            ->where(function ($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere('is_shared', true);
            })
            ->orderBy('position')
            ->get()
            ->map(fn(ProjectSavedView $v) => $this->format($v));

        return response()->json(['views' => $views]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'filters'        => 'nullable|array',
            'sort_by'        => 'nullable|string|max:50',
            'sort_direction' => 'nullable|in:asc,desc',
            'group_by'       => 'nullable|string|max:50',
            'view_type'      => 'nullable|string|max:20',
            'is_shared'      => 'sometimes|boolean',
        ]);

        $view = ProjectSavedView::create(array_merge($validated, [
            'project_id' => $project->id,
            'user_id'    => auth()->id(),
        ]));

        return response()->json(['success' => true, 'view' => $this->format($view)]);
    }

    public function update(Request $request, ProjectSavedView $view): JsonResponse
    {
        abort_unless($view->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'name'           => 'sometimes|string|max:100',
            'filters'        => 'nullable|array',
            'sort_by'        => 'nullable|string|max:50',
            'sort_direction' => 'nullable|in:asc,desc',
            'group_by'       => 'nullable|string|max:50',
            'view_type'      => 'nullable|string|max:20',
            'is_shared'      => 'sometimes|boolean',
        ]);

        $view->update($validated);

        return response()->json(['success' => true, 'view' => $this->format($view)]);
    }

    public function destroy(ProjectSavedView $view): JsonResponse
    {
        abort_unless($view->user_id === auth()->id(), 403);

        $view->delete();

        return response()->json(['success' => true]);
    }

    private function format(ProjectSavedView $view): array
    {
        return [
            'id'             => $view->id,
            'name'           => $view->name,
            'filters'        => $view->filters,
            'sort_by'        => $view->sort_by,
            'sort_direction' => $view->sort_direction,
            'group_by'       => $view->group_by,
            'view_type'      => $view->view_type,
            'is_shared'      => $view->is_shared,
            'is_mine'        => $view->user_id === auth()->id(),
        ];
    }
}
