<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct(protected OrganizationService $orgService) {}

    public function create()
    {
        return view('organizations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $org = $this->orgService->createForUser($request->user(), $validated);

        session(['current_org_id' => $org->id]);

        return redirect()->route('hub')->with('success', 'Organization created! Welcome to SmartSuite.');
    }

    public function manage(Organization $organization)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);

        return view('organizations.manage', [
            'organization' => $organization,
        ]);
    }

    public function show(Organization $organization)
    {
        abort_unless($organization->hasUser(auth()->user()), 403);

        $organization->load(['members', 'workspaces', 'subscriptions.product']);

        return view('organizations.show', [
            'organization' => $organization,
            'workspaces' => $organization->workspaces,
            'productConfig' => config('products'),
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $organization->update($validated);

        return back()->with('success', 'Organization updated.');
    }

    public function switchOrganization(Request $request, Organization $organization)
    {
        abort_unless($organization->hasUser(auth()->user()), 403);

        session(['current_org_id' => $organization->id]);

        return redirect()->route('hub');
    }
}
