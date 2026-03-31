<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Card;
use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index(Board $board)
    {
        return response()->json($board->labels);
    }

    public function store(Request $request, Board $board)
    {
        abort_unless($board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'color' => 'required|string|max:30',
        ]);

        $label = Label::create([...$validated, 'board_id' => $board->id]);

        return response()->json($label, 201);
    }

    public function update(Request $request, Label $label)
    {
        abort_unless($label->board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'color' => 'sometimes|string|max:30',
        ]);

        $label->update($validated);

        return response()->json($label);
    }

    public function destroy(Label $label)
    {
        abort_unless($label->board->canEdit(auth()->user()), 403);
        $label->cards()->detach();
        $label->delete();
        return response()->json(['success' => true]);
    }

    public function toggleCard(Request $request, Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate(['label_id' => 'required|integer|exists:labels,id']);

        if ($card->labels()->where('label_id', $validated['label_id'])->exists()) {
            $card->labels()->detach($validated['label_id']);
            $attached = false;
        } else {
            $card->labels()->attach($validated['label_id']);
            $attached = true;
        }

        return response()->json(['attached' => $attached, 'labels' => $card->fresh()->labels]);
    }
}
