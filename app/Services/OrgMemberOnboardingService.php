<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

class OrgMemberOnboardingService
{
    /**
     * Provision a newly added org member across all subscribed products.
     * Called when a user joins an organization (invitation accept, admin add, etc.)
     */
    public function provisionMember(Organization $org, User $user, string $role = 'member'): void
    {
        $this->ensureEmployeeProfile($org, $user);
        $this->addToDefaultWorkspace($org, $user);
        $this->assignDefaultRole($org, $user, $role);
    }

    /**
     * Auto-create an EmployeeProfile if the org has an HR subscription.
     */
    protected function ensureEmployeeProfile(Organization $org, User $user): void
    {
        if (!$org->hasProduct('hr')) {
            return;
        }

        EmployeeProfile::firstOrCreate(
            ['organization_id' => $org->id, 'user_id' => $user->id],
            [
                'employee_id'   => 'EMP-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'status'        => 'active',
                'date_of_joining' => now()->toDateString(),
            ]
        );
    }

    /**
     * Add user to the org's default workspace (first workspace) if Board product is active.
     */
    protected function addToDefaultWorkspace(Organization $org, User $user): void
    {
        if (!$org->hasProduct('board')) {
            return;
        }

        $workspace = Workspace::where('organization_id', $org->id)->first();
        if (!$workspace) {
            return;
        }

        $alreadyMember = $workspace->members()->where('user_id', $user->id)->exists();
        if (!$alreadyMember) {
            $workspace->members()->attach($user->id, [
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Assign the correct system role in organization_member_roles.
     */
    protected function assignDefaultRole(Organization $org, User $user, string $pivotRole): void
    {
        $roleSlug = match ($pivotRole) {
            'owner' => 'owner',
            'admin' => 'admin',
            default => 'member',
        };

        $role = $org->roles()->where('slug', $roleSlug)->first();
        if (!$role) {
            return;
        }

        $exists = \DB::table('organization_member_roles')
            ->where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->exists();

        if (!$exists) {
            \DB::table('organization_member_roles')->insert([
                'organization_id' => $org->id,
                'user_id'         => $user->id,
                'role_id'         => $role->id,
            ]);
        }
    }

    /**
     * Deactivate a member across all products.
     */
    public function deactivateMember(Organization $org, User $user): void
    {
        // Deactivate employee profile
        EmployeeProfile::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->update(['status' => 'inactive', 'deactivated_at' => now()]);

        // Remove from org member roles
        \DB::table('organization_member_roles')
            ->where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->delete();
    }
}
