<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdminAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('organizations')
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

        Auth::login(User::find($realAdminId));

        session()->forget('super_admin_impersonating');
        session()->forget('super_admin_impersonating_name');

        return redirect()->route('super-admin.dashboard');
    }
}
