<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectMilestoneController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date'    => 'nullable|date',
        ]);

        $milestone = $project->milestones()->create($validated);

        return response()->json(['success' => true, 'milestone' => $milestone]);
    }

    public function update(Request $request, Milestone $milestone): JsonResponse
    {
        abort_unless($milestone->project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date'    => 'nullable|date',
            'status'      => 'sometimes|in:open,completed',
        ]);

        $milestone->update($validated);

        return response()->json(['success' => true, 'milestone' => $milestone]);
    }

    public function destroy(Milestone $milestone): JsonResponse
    {
        abort_unless($milestone->project->canEdit(auth()->user()), 403);

        $milestone->delete();

        return response()->json(['success' => true]);
    }
}
