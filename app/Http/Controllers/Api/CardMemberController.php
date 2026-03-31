<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;

class CardMemberController extends Controller
{
    public function index(Card $card)
    {
        abort_unless($card->board->canAccess(auth()->user()), 403);
        return response()->json($card->members);
    }

    public function toggle(Request $request, Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate(['user_id' => 'required|integer|exists:users,id']);

        if ($card->members()->where('user_id', $validated['user_id'])->exists()) {
            $card->members()->detach($validated['user_id']);
            $attached = false;
        } else {
            $card->members()->attach($validated['user_id']);
            $attached = true;
        }

        return response()->json(['attached' => $attached, 'members' => $card->fresh()->members]);
    }
}
