<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkspaceMemberController extends Controller
{
    public function store(Request $request, Workspace $workspace)
    {
        abort_unless($workspace->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'in:admin,normal',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($workspace->hasUser($user)) {
            return back()->withErrors(['email' => 'User is already a member.']);
        }

        $workspace->members()->attach($user->id, ['role' => $validated['role'] ?? 'normal']);

        return back()->with('success', 'Member added.');
    }

    public function update(Request $request, Workspace $workspace, User $member)
    {
        abort_unless($workspace->isAdmin(auth()->user()), 403);

        $validated = $request->validate(['role' => 'required|in:admin,normal']);

        $workspace->members()->updateExistingPivot($member->id, ['role' => $validated['role']]);

        return back()->with('success', 'Role updated.');
    }

    public function destroy(Workspace $workspace, User $member)
    {
        abort_unless($workspace->isAdmin(auth()->user()) || $member->id === auth()->id(), 403);

        if ($workspace->owner_id === $member->id) {
            return back()->withErrors(['error' => 'Cannot remove workspace owner.']);
        }

        $workspace->members()->detach($member->id);

        return back()->with('success', 'Member removed.');
    }
}
