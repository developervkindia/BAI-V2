<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InvitationController;
use App\Models\PlatformInvitation;
use App\Models\User;
use App\Services\OrganizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'invitationEmail' => session('invitation_email'),
            'invitationBoard' => session('invitation_board'),
            'invitationInviter' => session('invitation_inviter'),
            'platformInviteEmail' => session('platform_invite_email'),
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        // Handle platform invitation — auto-create org and redirect to plan selection
        $platformToken = session('platform_invite_token');
        if ($platformToken) {
            $invitation = PlatformInvitation::where('token', $platformToken)
                ->where('status', 'pending')
                ->first();

            if ($invitation) {
                $invitation->update([
                    'status' => 'accepted',
                    'accepted_at' => now(),
                ]);

                $orgService = app(OrganizationService::class);
                $org = $orgService->createForUser($user, [
                    'name' => $user->name."'s Organization",
                ]);

                session(['current_org_id' => $org->id]);

                if ($invitation->promo_code) {
                    session(['onboarding_promo_code' => $invitation->promo_code]);
                }

                session()->forget(['platform_invite_token', 'platform_invite_email', 'platform_invite_promo']);

                return redirect()->route('onboarding.plans');
            }
        }

        // Process pending board invitation — returns board ID if any
        $boardId = InvitationController::processPendingInvitation($user);

        if ($boardId) {
            return redirect()->route('boards.show', $boardId);
        }

        return redirect()->route('hub');
    }
}
