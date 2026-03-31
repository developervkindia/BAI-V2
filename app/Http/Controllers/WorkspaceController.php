<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $workspace = Workspace::create([
            ...$validated,
            'owner_id' => $request->user()->id,
        ]);

        $workspace->members()->attach($request->user()->id, ['role' => 'admin']);

        return redirect()->route('workspaces.show', $workspace);
    }

    public function show(Workspace $workspace)
    {
        abort_unless($workspace->hasUser(auth()->user()), 403);

        $workspace->load(['boards' => fn($q) => $q->where('is_archived', false), 'members']);

        return view('workspaces.show', [
            'workspace' => $workspace,
            'workspaces' => auth()->user()->allWorkspaces(),
        ]);
    }

    public function update(Request $request, Workspace $workspace)
    {
        abort_unless($workspace->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $workspace->update($validated);

        return back()->with('success', 'Workspace updated.');
    }

    public function destroy(Workspace $workspace)
    {
        abort_unless($workspace->owner_id === auth()->id(), 403);
        $workspace->delete();
        return redirect()->route('dashboard')->with('success', 'Workspace deleted.');
    }
}
