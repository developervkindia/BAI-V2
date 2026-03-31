<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Models\Role;
use App\Models\EmployeeProfile;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request, Organization $organization)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);

        $query = $organization->members()->with([
            'employeeProfiles' => function ($q) use ($organization) {
                $q->where('organization_id', $organization->id);
            },
            'organizationRoles' => function ($q) use ($organization) {
                $q->wherePivot('organization_id', $organization->id);
            },
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($department = $request->input('department')) {
            $query->whereHas('employeeProfiles', function ($q) use ($organization, $department) {
                $q->where('organization_id', $organization->id)
                  ->where('department', $department);
            });
        }

        if ($status = $request->input('status')) {
            $query->whereHas('employeeProfiles', function ($q) use ($organization, $status) {
                $q->where('organization_id', $organization->id)
                  ->where('status', $status);
            });
        }

        $members = $query->paginate(20)->appends($request->query());
        $roles = $organization->roles()->get();

        return view('organizations.users.index', compact('organization', 'members', 'roles'));
    }

    public function show(Organization $organization, User $user)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);

        $user->load(['employeeProfiles' => function ($q) use ($organization) {
            $q->where('organization_id', $organization->id)
              ->with(['education', 'experience', 'documents', 'assets', 'skills']);
        }]);

        $userRoles = $user->organizationRoles()
            ->wherePivot('organization_id', $organization->id)
            ->get();

        return view('organizations.users.show', compact('organization', 'user', 'userRoles'));
    }

    public function edit(Organization $organization, User $user)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);

        $user->load(['employeeProfiles' => function ($q) use ($organization) {
            $q->where('organization_id', $organization->id);
        }]);

        $profile = $user->employeeProfiles->first();
        $roles = $organization->roles()->get();
        $userRoleIds = \Illuminate\Support\Facades\DB::table('organization_member_roles')
            ->where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->pluck('role_id')
            ->toArray();
        $members = $organization->members()->where('users.id', '!=', $user->id)->get();

        return view('organizations.users.edit', compact('organization', 'user', 'profile', 'roles', 'userRoleIds', 'members'));
    }

    public function update(Request $request, Organization $organization, User $user)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'date_of_joining' => 'nullable|date',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'marital_status' => 'nullable|string|max:50',
            'blood_group' => 'nullable|string|max:10',
            'nationality' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'personal_email' => 'nullable|email|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'current_address' => 'nullable|string|max:1000',
            'permanent_address' => 'nullable|string|max:1000',
            'reporting_manager_id' => 'nullable|exists:users,id',
            'employment_type' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,inactive,on_leave,terminated',
        ]);

        $user->update(['name' => $validated['name']]);

        $profileData = collect($validated)->except(['name'])->toArray();

        EmployeeProfile::updateOrCreate(
            [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
            ],
            $profileData
        );

        return redirect()->back()->with('success', 'User profile updated successfully.');
    }

    public function updateRole(Request $request, Organization $organization, User $user)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'role_ids' => 'array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $roleIds = $validated['role_ids'] ?? [];

        // Verify all roles belong to this organization
        $validRoles = $organization->roles()->whereIn('id', $roleIds)->get();

        // Sync roles for this user in this organization
        $user->organizationRoles($organization)->sync($validRoles->pluck('id'));

        // Determine highest role level and update system_role in pivot
        $highestRole = $validRoles->sortByDesc('level')->first();
        $systemRole = 'member';

        if ($highestRole && $highestRole->level >= 90) {
            $systemRole = 'admin';
        }

        $organization->members()->updateExistingPivot($user->id, [
            'system_role' => $systemRole,
        ]);

        return redirect()->back()->with('success', 'User roles updated successfully.');
    }

    public function deactivate(Organization $organization, User $user)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);
        abort_if($organization->owner_id === $user->id, 403, 'Cannot deactivate the organization owner.');

        $profile = EmployeeProfile::where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->first();

        if ($profile) {
            $profile->update([
                'status' => 'inactive',
                'deactivated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'User deactivated successfully.');
    }

    public function activate(Organization $organization, User $user)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);

        $profile = EmployeeProfile::where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->first();

        if ($profile) {
            $profile->update([
                'status' => 'active',
                'deactivated_at' => null,
            ]);
        }

        return redirect()->back()->with('success', 'User activated successfully.');
    }
}
