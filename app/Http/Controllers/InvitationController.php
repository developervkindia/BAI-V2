<?php

namespace App\Http\Controllers;

use App\Models\BoardInvitation;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function accept(string $token)
    {
        $invitation = BoardInvitation::where('token', $token)
            ->with('board.workspace', 'inviter')
            ->first();

        if (!$invitation) {
            return redirect()->route('login')->with('error', 'This invitation link is invalid.');
        }

        // If already accepted — redirect to board (for existing users who got the email)
        if ($invitation->status === 'accepted') {
            if (auth()->check()) {
                return redirect()->route('boards.show', $invitation->board);
            }
            return redirect()->route('login')->with('info', 'Sign in to access this board.');
        }

        if ($invitation->status === 'declined') {
            return redirect()->route('login')->with('error', 'This invitation has been cancelled.');
        }

        // Status is 'pending'
        if ($invitation->isExpired()) {
            return redirect()->route('login')->with('error', 'This invitation has expired. Please ask the board owner to send a new one.');
        }

        // LOGGED IN user — accept immediately and go to board
        if (auth()->check()) {
            $this->processInvitation($invitation, auth()->user());
            return redirect()->route('boards.show', $invitation->board)
                ->with('success', 'You have joined the board "' . $invitation->board->name . '"!');
        }

        // NOT LOGGED IN — check if user exists in our system
        $existingUser = \App\Models\User::where('email', $invitation->email)->first();

        // Store invitation in session
        session([
            'pending_invitation' => $token,
            'invitation_email' => $invitation->email,
            'invitation_board' => $invitation->board->name,
            'invitation_inviter' => $invitation->inviter->name,
        ]);

        if ($existingUser) {
            // User exists but not logged in — send to login
            return redirect()->route('login')
                ->with('info', 'Sign in to join the board "' . $invitation->board->name . '".');
        }

        // User doesn't exist — send to register with locked email
        return redirect()->route('register');
    }

    public function decline(string $token)
    {
        $invitation = BoardInvitation::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if ($invitation) {
            $invitation->update(['status' => 'declined']);
        }

        return redirect()->route('home')->with('info', 'Invitation declined.');
    }

    public static function processInvitation(BoardInvitation $invitation, $user): void
    {
        if (!$invitation->board->members()->where('user_id', $user->id)->exists()) {
            $invitation->board->members()->attach($user->id, ['role' => $invitation->role]);
        }
        $invitation->update(['status' => 'accepted']);
    }

    public static function processPendingInvitation($user): ?int
    {
        $token = session('pending_invitation');
        if (!$token) return null;

        $invitation = BoardInvitation::where('token', $token)
            ->where('status', 'pending')
            ->with('board')
            ->first();

        $boardId = null;
        if ($invitation && !$invitation->isExpired()) {
            self::processInvitation($invitation, $user);
            $boardId = $invitation->board_id;
        }

        session()->forget(['pending_invitation', 'invitation_email', 'invitation_board', 'invitation_inviter']);

        return $boardId;
    }
}
