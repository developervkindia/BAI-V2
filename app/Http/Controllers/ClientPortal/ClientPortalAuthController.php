<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientPortalAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('client_portal')->check()) {
            return redirect()->route('client-portal.home');
        }

        return view('client-portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('client_portal')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            Auth::guard('client_portal')->user()->update(['last_login_at' => now()]);

            return redirect()->intended(route('client-portal.home'));
        }

        return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('client_portal')->logout();

        return redirect()->route('client-portal.login');
    }
}
