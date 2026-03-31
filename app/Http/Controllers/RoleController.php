<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Organization $organization)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);

        $roles = $organization->roles()
            ->withCount(['users', 'permissions'])
            ->get();

        $permissions = app(\App\Services\PermissionService::class)->allPermissionsGroupedByProduct();

        return view('organizations.roles.index', compact('organization', 'roles', 'permissions'));
    }

    public function create(Organization $organization)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);

        $permissions = app(\App\Services\PermissionService::class)->allPermissionsGroupedByProduct();
        $role = null;

        return view('organizations.roles.form', compact('organization', 'permissions', 'role'));
    }

    public function store(Request $request, Organization $organization)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = $organization->roles()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_system' => false,
            'level' => 50,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('organizations.roles.index', $organization)
            ->with('success', 'Role created successfully.');
    }

    public function edit(Organization $organization, Role $role)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);
        abort_unless($role->organization_id === $organization->id, 404);

        $role->load('permissions');
        $permissions = app(\App\Services\PermissionService::class)->allPermissionsGroupedByProduct();

        return view('organizations.roles.form', compact('organization', 'role', 'permissions'));
    }

    public function update(Request $request, Organization $organization, Role $role)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);
        abort_unless($role->organization_id === $organization->id, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if (!$role->is_system) {
            $role->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);
        } else {
            $role->update([
                'description' => $validated['description'] ?? $role->description,
            ]);
        }

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->back()->with('success', 'Role updated successfully.');
    }

    public function destroy(Organization $organization, Role $role)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);
        abort_unless($role->organization_id === $organization->id, 404);
        abort_if($role->is_system, 403, 'System roles cannot be deleted.');

        $role->users()->detach();
        $role->delete();

        return redirect()->back()->with('success', 'Role deleted successfully.');
    }
}
