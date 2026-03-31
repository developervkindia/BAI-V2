<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardVote;

class CardVoteController extends Controller
{
    public function toggle(Card $card)
    {
        abort_unless($card->board->canAccess(auth()->user()), 403);

        $existing = CardVote::where('card_id', $card->id)->where('user_id', auth()->id())->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'voted' => false,
                'vote_count' => $card->votes()->count(),
            ]);
        }

        CardVote::create(['card_id' => $card->id, 'user_id' => auth()->id(), 'created_at' => now()]);

        return response()->json([
            'voted' => true,
            'vote_count' => $card->votes()->count(),
        ]);
    }
}
