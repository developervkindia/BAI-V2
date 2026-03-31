<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Card;
use App\Services\PositionService;
use Illuminate\Http\Request;

class InboundEmailController extends Controller
{
    public function receive(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'from' => 'required|email',
            'subject' => 'required|string|max:500',
            'body' => 'nullable|string|max:10000',
        ]);

        $board = Board::where('email_address', $validated['to'])->first();

        if (!$board) {
            return response()->json(['error' => 'Board not found'], 404);
        }

        // Get the first active list
        $list = $board->lists()->where('is_archived', false)->orderBy('position')->first();

        if (!$list) {
            return response()->json(['error' => 'No active list found'], 422);
        }

        $card = Card::create([
            'board_list_id' => $list->id,
            'board_id' => $board->id,
            'title' => $validated['subject'],
            'description' => $validated['body'] ?? '',
            'position' => PositionService::getNextPosition($list->cards()),
            'created_by' => $board->created_by,
        ]);

        return response()->json($card, 201);
    }
}
