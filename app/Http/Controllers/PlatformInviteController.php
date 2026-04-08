<?php

namespace App\Http\Controllers;

use App\Models\PlatformInvitation;

class PlatformInviteController extends Controller
{
    public function accept(string $token)
    {
        $invitation = PlatformInvitation::where('token', $token)->first();

        if (! $invitation) {
            return redirect()->route('login')->with('error', 'Invalid invitation link.');
        }

        if ($invitation->status !== 'pending') {
            return redirect()->route('login')->with('error', 'This invitation has already been used.');
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);

            return redirect()->route('login')->with('error', 'This invitation has expired.');
        }

        session([
            'platform_invite_token' => $invitation->token,
            'platform_invite_email' => $invitation->email,
            'platform_invite_promo' => $invitation->promo_code,
        ]);

        return redirect()->route('register');
    }
}
