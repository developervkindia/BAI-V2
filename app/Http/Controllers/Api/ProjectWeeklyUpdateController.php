<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectWeeklyUpdate;
use Illuminate\Http\Request;

class ProjectWeeklyUpdateController extends Controller
{
    public function store(Request $request, Project $project)
    {
        abort_unless($project->canAccess($request->user()), 403);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'period_type' => 'required|in:weekly,biweekly',
            'week_start'  => 'required|date',
            'week_end'    => 'required|date|after_or_equal:week_start',
            'summary'     => 'required|string',
            'next_steps'  => 'nullable|string',
            'blockers'    => 'nullable|string',
        ]);

        $update = $project->weeklyUpdates()->create(array_merge($data, [
            'created_by' => $request->user()->id,
        ]));

        $update->load('author', 'qaApprover');

        return response()->json(['success' => true, 'update' => $update]);
    }

    public function update(Request $request, ProjectWeeklyUpdate $update)
    {
        abort_unless($update->project->canAccess($request->user()), 403);

        $data = $request->validate([
            'title'                  => 'sometimes|string|max:255',
            'summary'                => 'sometimes|string',
            'next_steps'             => 'nullable|string',
            'blockers'               => 'nullable|string',
            'qa_approve'             => 'sometimes|boolean',
            'share_with_client'      => 'sometimes|boolean',
        ]);

        if (!empty($data['qa_approve'])) {
            $update->qa_approved_by = $request->user()->id;
            $update->qa_approved_at = now();
        }

        if (!empty($data['share_with_client'])) {
            $update->shared_with_client_at = now();
        }

        $fields = array_intersect_key($data, array_flip(['title', 'summary', 'next_steps', 'blockers']));
        $update->fill($fields)->save();
        $update->load('author', 'qaApprover');

        return response()->json(['success' => true, 'update' => $update]);
    }

    public function destroy(Request $request, ProjectWeeklyUpdate $update)
    {
        abort_unless($update->project->canEdit($request->user()), 403);
        $update->delete();

        return response()->json(['success' => true]);
    }
}
