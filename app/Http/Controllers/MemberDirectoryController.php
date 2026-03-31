<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;

class MemberDirectoryController extends Controller
{
    public function index(Request $request, Workspace $workspace)
    {
        abort_unless($workspace->hasUser(auth()->user()), 403);

        $query = $request->input('q', '');
        $roleFilter = $request->input('role', '');

        $members = $workspace->members()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q2) use ($query) {
                    $q2->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                });
            })
            ->when($roleFilter, function ($q) use ($roleFilter) {
                $q->wherePivot('role', $roleFilter);
            })
            ->withPivot('role')
            ->get();

        // Also include owner
        $owner = $workspace->owner;

        $groups = $workspace->groups()->with('members:id,name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'members' => $members,
                'owner' => $owner,
                'groups' => $groups,
            ]);
        }

        return view('workspaces.members', [
            'workspace' => $workspace,
            'members' => $members,
            'owner' => $owner,
            'groups' => $groups,
            'query' => $query,
            'roleFilter' => $roleFilter,
            'workspaces' => auth()->user()->allWorkspaces(),
        ]);
    }
}
