<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectLabel;
use App\Models\ProjectTask;
use Illuminate\Http\Request;

class ProjectLabelController extends Controller
{
    public function index(Project $project)
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        return response()->json($project->labels()->orderBy('name')->get());
    }

    public function store(Request $request, Project $project)
    {
        abort_unless($project->isManager(auth()->user()), 403);

        $data = $request->validate([
            'name'  => 'required|string|max:80',
            'color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        $label = $project->labels()->create($data);

        return response()->json($label, 201);
    }

    public function update(Request $request, ProjectLabel $label)
    {
        abort_unless($label->project->isManager(auth()->user()), 403);

        $data = $request->validate([
            'name'  => 'sometimes|required|string|max:80',
            'color' => 'sometimes|required|string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        $label->update($data);

        return response()->json($label);
    }

    public function destroy(ProjectLabel $label)
    {
        abort_unless($label->project->isManager(auth()->user()), 403);

        $label->tasks()->detach();
        $label->delete();

        return response()->json(['success' => true]);
    }

    public function toggle(Request $request, ProjectTask $task)
    {
        abort_unless($task->project->canEdit(auth()->user()), 403);

        $request->validate(['label_id' => 'required|integer']);
        $labelId = $request->label_id;

        // Ensure label belongs to same project
        $label = ProjectLabel::where('id', $labelId)
            ->where('project_id', $task->project_id)
            ->firstOrFail();

        $task->labels()->toggle($label->id);

        return response()->json([
            'labels' => $task->labels()->get()->map(fn($l) => [
                'id'    => $l->id,
                'name'  => $l->name,
                'color' => $l->color,
            ]),
        ]);
    }
}
