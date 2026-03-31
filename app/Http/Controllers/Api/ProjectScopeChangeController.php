<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectScopeChange;
use Illuminate\Http\Request;

class ProjectScopeChangeController extends Controller
{
    public function store(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'required|in:addition,reduction,timeline,budget',
            'cost_impact' => 'nullable|numeric|min:0',
            'days_impact' => 'nullable|integer',
        ]);

        $change = $project->scopeChanges()->create(array_merge($data, [
            'requested_by' => $request->user()->id,
            'status'       => 'pending',
        ]));

        $change->load('requester');

        return response()->json(['success' => true, 'change' => $change]);
    }

    public function update(Request $request, ProjectScopeChange $change)
    {
        abort_unless($change->project->canEdit($request->user()), 403);

        $data = $request->validate([
            'status'      => 'sometimes|in:pending,approved,rejected',
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'cost_impact' => 'nullable|numeric|min:0',
            'days_impact' => 'nullable|integer',
        ]);

        if (isset($data['status']) && in_array($data['status'], ['approved', 'rejected'])) {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
        }

        $change->update($data);
        $change->load('requester', 'approver');

        return response()->json(['success' => true, 'change' => $change]);
    }

    public function destroy(Request $request, ProjectScopeChange $change)
    {
        abort_unless($change->project->canEdit($request->user()), 403);
        $change->delete();

        return response()->json(['success' => true]);
    }
}
