<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SuperAdminAuditLog;
use Illuminate\Http\Request;

class SuperAdminOrganizationController extends Controller
{
    public function index(Request $request)
    {
        $organizations = Organization::withCount(['members', 'subscriptions'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->paginate(25);

        return view('super-admin.organizations.index', compact('organizations'));
    }

    public function show(Organization $organization)
    {
        $organization->load(['members', 'subscriptions.product']);

        $projectsCount = \App\Models\Project::where('organization_id', $organization->id)->count();
        $boardsCount = \App\Models\Board::whereIn('workspace_id', $organization->workspaces()->pluck('id'))->count();

        return view('super-admin.organizations.show', compact(
            'organization',
            'projectsCount',
            'boardsCount'
        ));
    }

    public function activate(Organization $organization)
    {
        $organization->update([
            'is_active' => true,
            'deactivated_at' => null,
        ]);

        SuperAdminAuditLog::record(auth()->user(), 'org.activated', $organization);

        return redirect()->back()->with('success', 'Organization activated successfully.');
    }

    public function deactivate(Organization $organization)
    {
        $organization->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);

        SuperAdminAuditLog::record(auth()->user(), 'org.deactivated', $organization);

        return redirect()->back()->with('success', 'Organization deactivated successfully.');
    }
}
