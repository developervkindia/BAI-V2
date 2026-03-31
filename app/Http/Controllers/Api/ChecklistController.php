<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Services\PositionService;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function store(Request $request, Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate(['name' => 'required|string|max:255']);

        $checklist = Checklist::create([
            'card_id' => $card->id,
            'name' => $validated['name'],
            'position' => PositionService::getNextPosition($card->checklists()),
        ]);

        return response()->json($checklist->load('items'), 201);
    }

    public function update(Request $request, Checklist $checklist)
    {
        abort_unless($checklist->card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate(['name' => 'required|string|max:255']);
        $checklist->update($validated);

        return response()->json($checklist);
    }

    public function destroy(Checklist $checklist)
    {
        abort_unless($checklist->card->board->canEdit(auth()->user()), 403);
        $checklist->items()->delete();
        $checklist->delete();
        return response()->json(['success' => true]);
    }

    public function storeItem(Request $request, Checklist $checklist)
    {
        abort_unless($checklist->card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate(['content' => 'required|string|max:500']);

        $item = ChecklistItem::create([
            'checklist_id' => $checklist->id,
            'content' => $validated['content'],
            'position' => PositionService::getNextPosition($checklist->items()),
        ]);

        return response()->json($item, 201);
    }

    public function updateItem(Request $request, ChecklistItem $item)
    {
        abort_unless($item->checklist->card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'content' => 'sometimes|string|max:500',
            'is_checked' => 'sometimes|boolean',
        ]);

        $item->update($validated);

        return response()->json($item);
    }

    public function toggleItem(ChecklistItem $item)
    {
        abort_unless($item->checklist->card->board->canEdit(auth()->user()), 403);

        $item->update(['is_checked' => !$item->is_checked]);

        return response()->json($item);
    }

    public function destroyItem(ChecklistItem $item)
    {
        abort_unless($item->checklist->card->board->canEdit(auth()->user()), 403);
        $item->delete();
        return response()->json(['success' => true]);
    }
}
