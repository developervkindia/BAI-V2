<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\Role;
use App\Models\User;
use App\Models\EmployeeProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationInvitationController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'email' => 'required|email',
            'system_role' => 'required|in:admin,member',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        // Check if the email is already a member
        $alreadyMember = $organization->members()->where('email', $validated['email'])->exists();
        abort_if($alreadyMember, 422, 'This email is already a member of the organization.');

        $invitation = $organization->invitations()->create([
            'email' => $validated['email'],
            'system_role' => $validated['system_role'],
            'role_id' => $validated['role_id'] ?? null,
            'token' => Str::random(64),
            'invited_by' => auth()->id(),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitation sent successfully.',
            'invitation' => $invitation,
        ]);
    }

    public function resend(Organization $organization, OrganizationInvitation $invitation)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);
        abort_unless($invitation->organization_id === $organization->id, 404);

        $invitation->update([
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitation resent successfully.',
        ]);
    }

    public function cancel(Organization $organization, OrganizationInvitation $invitation)
    {
        abort_unless($organization->isAdmin(auth()->user()), 403);
        abort_unless($invitation->organization_id === $organization->id, 404);

        $invitation->update([
            'status' => 'declined',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitation cancelled successfully.',
        ]);
    }

    public function accept(string $token)
    {
        $invitation = OrganizationInvitation::where('token', $token)->firstOrFail();

        // Check if expired
        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            abort(410, 'This invitation has expired.');
        }

        // Check if not pending
        if ($invitation->status !== 'pending') {
            return view('invitations.already-handled', [
                'message' => 'This invitation has already been ' . $invitation->status . '.',
            ]);
        }

        $organization = $invitation->organization;
        $user = User::where('email', $invitation->email)->first();

        if ($user) {
            // Add user to organization members
            $organization->members()->syncWithoutDetaching([
                $user->id => [
                    'system_role' => $invitation->system_role ?? 'member',
                ],
            ]);

            // Create employee profile
            EmployeeProfile::firstOrCreate([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
            ], [
                'status' => 'active',
            ]);

            // Assign role if specified
            if ($invitation->role_id) {
                $user->organizationRoles($organization)->syncWithoutDetaching([$invitation->role_id]);
            }

            // Mark invitation as accepted
            $invitation->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            return redirect()->route('hub')->with('success', 'You have joined ' . $organization->name . '.');
        }

        // No user exists - redirect to registration with prefilled email
        $invitation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return redirect()->route('register', ['email' => $invitation->email, 'invitation' => $invitation->token])
            ->with('info', 'Please create an account to join ' . $organization->name . '.');
    }
}
