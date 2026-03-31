<?php

namespace App\Http\Controllers;

use App\Mail\BoardInvitationMail;
use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BoardMemberController extends Controller
{
    public function index(Board $board)
    {
        abort_unless($board->canAccess(auth()->user()), 403);

        $members = $board->members->map(fn($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'email' => $m->email,
            'avatar_url' => $m->avatar_url,
            'role' => $m->pivot->role,
            'type' => 'member',
        ]);

        $pendingInvites = BoardInvitation::where('board_id', $board->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->get()
            ->map(fn($inv) => [
                'id' => 'invite-' . $inv->id,
                'invite_id' => $inv->id,
                'name' => $inv->email,
                'email' => $inv->email,
                'avatar_url' => null,
                'role' => $inv->role,
                'type' => 'pending',
                'expires_at' => $inv->expires_at->format('M j, Y'),
            ]);

        return response()->json($members->concat($pendingInvites)->values());
    }

    public function store(Request $request, Board $board)
    {
        abort_unless($board->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'in:admin,normal,observer',
        ]);

        $email = strtolower(trim($validated['email']));
        $role = $validated['role'] ?? 'normal';

        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $board->members()->where('user_id', $existingUser->id)->exists()) {
            return response()->json(['error' => 'This user is already a board member.'], 422);
        }

        $existingInvite = BoardInvitation::where('board_id', $board->id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvite) {
            return response()->json(['error' => 'An invitation has already been sent to this email.'], 422);
        }

        if ($existingUser) {
            $board->members()->attach($existingUser->id, ['role' => $role]);

            $invitation = BoardInvitation::create([
                'board_id' => $board->id,
                'invited_by' => auth()->id(),
                'email' => $email,
                'role' => $role,
                'status' => 'accepted',
            ]);

            try { Mail::to($email)->send(new BoardInvitationMail($invitation)); } catch (\Exception $e) {}

            return response()->json([
                'success' => true,
                'message' => $existingUser->name . ' has been added to the board.',
                'member' => [
                    'id' => $existingUser->id,
                    'name' => $existingUser->name,
                    'email' => $existingUser->email,
                    'avatar_url' => $existingUser->avatar_url,
                    'role' => $role,
                    'type' => 'member',
                ],
            ], 201);
        }

        $invitation = BoardInvitation::create([
            'board_id' => $board->id,
            'invited_by' => auth()->id(),
            'email' => $email,
            'role' => $role,
        ]);

        try {
            Mail::to($email)->send(new BoardInvitationMail($invitation));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send invitation email.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invitation sent to ' . $email,
            'member' => [
                'id' => 'invite-' . $invitation->id,
                'invite_id' => $invitation->id,
                'name' => $email,
                'email' => $email,
                'avatar_url' => null,
                'role' => $role,
                'type' => 'pending',
                'expires_at' => $invitation->expires_at->format('M j, Y'),
            ],
        ], 201);
    }

    public function update(Request $request, Board $board, User $member)
    {
        abort_unless($board->isAdmin(auth()->user()), 403);

        $validated = $request->validate(['role' => 'required|in:admin,normal,observer']);

        // Can't demote the board creator
        if ($board->created_by === $member->id && $validated['role'] !== 'admin') {
            return response()->json(['error' => 'Cannot change the board creator\'s role.'], 422);
        }

        $board->members()->updateExistingPivot($member->id, ['role' => $validated['role']]);

        return response()->json(['success' => true]);
    }

    public function destroy(Board $board, User $member)
    {
        $user = auth()->user();
        // Admin can remove anyone (except creator), or member can remove themselves
        abort_unless($board->isAdmin($user) || $member->id === $user->id, 403);

        if ($board->created_by === $member->id) {
            return response()->json(['error' => 'Cannot remove the board creator.'], 422);
        }

        $board->members()->detach($member->id);

        return response()->json(['success' => true]);
    }

    public function cancelInvitation(Board $board, BoardInvitation $invitation)
    {
        abort_unless($board->isAdmin(auth()->user()), 403);
        abort_unless($invitation->board_id === $board->id, 404);

        $invitation->update(['status' => 'declined']);

        return response()->json(['success' => true]);
    }

    public function resendInvitation(Board $board, BoardInvitation $invitation)
    {
        abort_unless($board->isAdmin(auth()->user()), 403);
        abort_unless($invitation->board_id === $board->id, 404);

        $invitation->update(['expires_at' => now()->addDays(7)]);

        try {
            Mail::to($invitation->email)->send(new BoardInvitationMail($invitation));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to resend email.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Invitation resent.']);
    }
}
