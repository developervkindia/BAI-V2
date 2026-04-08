<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\PlatformInvitationMail;
use App\Models\PlatformInvitation;
use App\Models\SuperAdminAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SuperAdminUserController extends Controller
{
    public function index(Request $request)
    {
        // Only show organization owners (admins who registered and manage orgs)
        $users = User::with('organizations')
            ->where(function ($q) {
                $q->where('is_super_admin', true)
                  ->orWhereHas('ownedOrganizations');
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate(25);

        return view('super-admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['organizations', 'employeeProfiles']);

        return view('super-admin.users.show', compact('user'));
    }

    public function impersonate(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot impersonate yourself.');
        }

        session([
            'super_admin_impersonating' => auth()->id(),
            'super_admin_impersonating_name' => auth()->user()->name,
        ]);

        SuperAdminAuditLog::record(auth()->user(), 'user.impersonated', $user);

        Auth::login($user);

        return redirect()->route('hub');
    }

    public function stopImpersonating()
    {
        $realAdminId = session('super_admin_impersonating');

        if (! $realAdminId) {
            return redirect()->route('hub');
        }

        Auth::login(User::find($realAdminId));

        session()->forget('super_admin_impersonating');
        session()->forget('super_admin_impersonating_name');
        session()->forget('impersonated_user_name');
        session()->forget('current_org_id');

        return redirect()->route('super-admin.dashboard');
    }

    public function invite(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'promo_code' => 'nullable|string|max:100',
        ]);

        $existing = User::where('email', $validated['email'])->first();
        if ($existing) {
            return back()->with('error', 'A user with this email already exists.');
        }

        $pendingInvite = PlatformInvitation::where('email', $validated['email'])
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($pendingInvite) {
            return back()->with('error', 'A pending invitation already exists for this email.');
        }

        $invitation = PlatformInvitation::create([
            'email' => $validated['email'],
            'promo_code' => $validated['promo_code'] ?: null,
            'token' => Str::random(64),
            'status' => 'pending',
            'invited_by' => auth()->id(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($validated['email'])->send(new PlatformInvitationMail($invitation));

        SuperAdminAuditLog::record(auth()->user(), 'user.invited', $invitation);

        return back()->with('success', "Invitation sent to {$validated['email']}.");
    }
}
