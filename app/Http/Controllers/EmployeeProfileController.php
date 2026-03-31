<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use Illuminate\Http\Request;

class EmployeeProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $organization = $user->currentOrganization();

        $profile = EmployeeProfile::firstOrCreate(
            [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
            ],
            [
                'status' => 'active',
            ]
        );

        $profile->load(['education', 'experience', 'documents', 'assets', 'skills']);

        return view('profile.full', compact('profile', 'organization'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $organization = $user->currentOrganization();

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'personal_email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'marital_status' => 'nullable|string|max:50',
            'blood_group' => 'nullable|string|max:10',
            'nationality' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'current_address' => 'nullable|string|max:1000',
            'permanent_address' => 'nullable|string|max:1000',
        ]);

        $profile = EmployeeProfile::where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->firstOrFail();

        $profile->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
