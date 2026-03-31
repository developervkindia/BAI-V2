<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->isManager(auth()->user()), 403);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role'  => 'in:manager,member,viewer',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($project->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'User is already a member.'], 422);
        }

        $project->members()->attach($user->id, ['role' => $validated['role'] ?? 'member']);

        return response()->json([
            'success' => true,
            'member'  => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $validated['role'] ?? 'member'],
        ]);
    }

    public function destroy(Project $project, User $member): JsonResponse
    {
        abort_unless($project->isManager(auth()->user()), 403);

        if ($project->owner_id === $member->id) {
            return response()->json(['error' => 'Cannot remove project owner.'], 422);
        }

        $project->members()->detach($member->id);

        return response()->json(['success' => true]);
    }
}
