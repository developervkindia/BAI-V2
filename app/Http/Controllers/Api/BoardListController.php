<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardList;
use App\Services\PositionService;
use Illuminate\Http\Request;

class BoardListController extends Controller
{
    public function store(Request $request, Board $board)
    {
        abort_unless($board->canEdit(auth()->user()), 403);

        $validated = $request->validate(['name' => 'required|string|max:255']);

        $list = BoardList::create([
            'board_id' => $board->id,
            'name' => $validated['name'],
            'position' => PositionService::getNextPosition($board->lists()),
        ]);

        return response()->json($list->load('cards'), 201);
    }

    public function update(Request $request, BoardList $list)
    {
        abort_unless($list->board->canEdit(auth()->user()), 403);

        $validated = $request->validate(['name' => 'required|string|max:255']);
        $list->update($validated);

        return response()->json($list);
    }

    public function destroy(BoardList $list)
    {
        abort_unless($list->board->canEdit(auth()->user()), 403);
        $list->cards()->delete();
        $list->delete();
        return response()->json(['success' => true]);
    }

    public function archive(BoardList $list)
    {
        abort_unless($list->board->canEdit(auth()->user()), 403);
        $list->update(['is_archived' => true]);
        return response()->json(['success' => true]);
    }

    public function restore(BoardList $list)
    {
        abort_unless($list->board->canEdit(auth()->user()), 403);
        $list->update(['is_archived' => false]);
        return response()->json($list->load('cards'));
    }

    public function reorder(Request $request, Board $board)
    {
        abort_unless($board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:board_lists,id',
            'items.*.position' => 'required|numeric',
        ]);

        foreach ($validated['items'] as $item) {
            BoardList::where('id', $item['id'])->where('board_id', $board->id)
                ->update(['position' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }

    public function copy(Request $request, BoardList $list)
    {
        abort_unless($list->board->canEdit(auth()->user()), 403);

        $newList = $list->replicate();
        $newList->name = $list->name . ' (copy)';
        $newList->position = PositionService::getNextPosition($list->board->lists());
        $newList->save();

        foreach ($list->cards()->where('is_archived', false)->get() as $card) {
            $newCard = $card->replicate();
            $newCard->board_list_id = $newList->id;
            $newCard->save();

            $newCard->labels()->sync($card->labels->pluck('id'));

            foreach ($card->checklists as $checklist) {
                $newChecklist = $checklist->replicate();
                $newChecklist->card_id = $newCard->id;
                $newChecklist->save();

                foreach ($checklist->items as $item) {
                    $newItem = $item->replicate();
                    $newItem->checklist_id = $newChecklist->id;
                    $newItem->save();
                }
            }
        }

        return response()->json($newList->load('cards.labels', 'cards.members', 'cards.checklists.items'), 201);
    }

    public function moveAllCards(Request $request, BoardList $list)
    {
        abort_unless($list->board->canEdit(auth()->user()), 403);

        $validated = $request->validate(['target_list_id' => 'required|integer|exists:board_lists,id']);

        $targetList = BoardList::findOrFail($validated['target_list_id']);
        abort_unless($targetList->board_id === $list->board_id, 422);

        $position = PositionService::getNextPosition($targetList->cards());

        foreach ($list->cards()->where('is_archived', false)->orderBy('position')->get() as $card) {
            $card->update([
                'board_list_id' => $targetList->id,
                'position' => $position,
            ]);
            $position += PositionService::GAP;
        }

        return response()->json(['success' => true]);
    }
}
