<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardDependency;
use Illuminate\Http\Request;

class CardDependencyController extends Controller
{
    public function index(Card $card)
    {
        abort_unless($card->board->canAccess(auth()->user()), 403);

        $dependencies = $card->dependencies()->with('dependsOnCard:id,title,board_list_id')->get();
        $dependents = $card->dependents()->with('card:id,title,board_list_id')->get();

        return response()->json([
            'blocks' => $dependents,
            'blocked_by' => $dependencies,
        ]);
    }

    public function store(Request $request, Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'depends_on_card_id' => 'required|integer|exists:cards,id',
        ]);

        abort_if($card->id === (int)$validated['depends_on_card_id'], 422, 'A card cannot depend on itself.');

        $dep = CardDependency::firstOrCreate([
            'card_id' => $card->id,
            'depends_on_card_id' => $validated['depends_on_card_id'],
        ], ['created_at' => now()]);

        return response()->json($dep->load('dependsOnCard:id,title,board_list_id'), 201);
    }

    public function destroy(CardDependency $dependency)
    {
        $card = $dependency->card;
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $dependency->delete();
        return response()->json(['success' => true]);
    }
}
