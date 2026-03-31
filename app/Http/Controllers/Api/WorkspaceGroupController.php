<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Models\WorkspaceGroup;
use Illuminate\Http\Request;

class WorkspaceGroupController extends Controller
{
    public function index(Workspace $workspace)
    {
        abort_unless($workspace->hasUser(auth()->user()), 403);
        return response()->json($workspace->groups()->with('members:id,name,email,avatar_path')->get());
    }

    public function store(Request $request, Workspace $workspace)
    {
        abort_unless($workspace->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'integer|exists:users,id',
        ]);

        $group = $workspace->groups()->create(['name' => $validated['name']]);

        if (!empty($validated['member_ids'])) {
            $group->members()->sync($validated['member_ids']);
        }

        return response()->json($group->load('members:id,name,email,avatar_path'), 201);
    }

    public function update(Request $request, WorkspaceGroup $group)
    {
        abort_unless($group->workspace->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'integer|exists:users,id',
        ]);

        if (isset($validated['name'])) {
            $group->update(['name' => $validated['name']]);
        }

        if (isset($validated['member_ids'])) {
            $group->members()->sync($validated['member_ids']);
        }

        return response()->json($group->load('members:id,name,email,avatar_path'));
    }

    public function destroy(WorkspaceGroup $group)
    {
        abort_unless($group->workspace->isAdmin(auth()->user()), 403);
        $group->delete();
        return response()->json(['success' => true]);
    }
}
