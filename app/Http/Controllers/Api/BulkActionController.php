<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Card;
use Illuminate\Http\Request;

class BulkActionController extends Controller
{
    public function execute(Request $request, Board $board)
    {
        abort_unless($board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'card_ids' => 'required|array|min:1',
            'card_ids.*' => 'integer|exists:cards,id',
            'action' => 'required|string|in:move,archive,delete,add_label,remove_label,assign_member,remove_member',
            'board_list_id' => 'required_if:action,move|nullable|integer|exists:board_lists,id',
            'label_id' => 'required_if:action,add_label,remove_label|nullable|integer|exists:labels,id',
            'user_id' => 'required_if:action,assign_member,remove_member|nullable|integer|exists:users,id',
        ]);

        $cards = Card::whereIn('id', $validated['card_ids'])
            ->where('board_id', $board->id)
            ->get();

        $count = 0;
        foreach ($cards as $card) {
            switch ($validated['action']) {
                case 'move':
                    $card->update(['board_list_id' => $validated['board_list_id']]);
                    break;
                case 'archive':
                    $card->update(['is_archived' => true]);
                    break;
                case 'delete':
                    $card->delete();
                    break;
                case 'add_label':
                    $card->labels()->syncWithoutDetaching([$validated['label_id']]);
                    break;
                case 'remove_label':
                    $card->labels()->detach($validated['label_id']);
                    break;
                case 'assign_member':
                    $card->members()->syncWithoutDetaching([$validated['user_id']]);
                    break;
                case 'remove_member':
                    $card->members()->detach($validated['user_id']);
                    break;
            }
            $count++;
        }

        return response()->json(['success' => true, 'affected' => $count]);
    }
}
