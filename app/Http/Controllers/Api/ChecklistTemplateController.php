<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Card;
use App\Models\Checklist;
use App\Models\ChecklistTemplate;
use App\Services\PositionService;
use Illuminate\Http\Request;

class ChecklistTemplateController extends Controller
{
    public function index(Board $board)
    {
        abort_unless($board->canAccess(auth()->user()), 403);
        return response()->json($board->checklistTemplates);
    }

    public function store(Request $request, Board $board)
    {
        abort_unless($board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*' => 'string|max:500',
        ]);

        $template = ChecklistTemplate::create([
            'board_id' => $board->id,
            'name' => $validated['name'],
            'items' => $validated['items'],
        ]);

        return response()->json($template, 201);
    }

    public function storeFromChecklist(Checklist $checklist)
    {
        $card = $checklist->card;
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $items = $checklist->items()->orderBy('position')->pluck('content')->toArray();

        $template = ChecklistTemplate::create([
            'board_id' => $card->board_id,
            'name' => $checklist->name,
            'items' => $items,
        ]);

        return response()->json($template, 201);
    }

    public function apply(Request $request, ChecklistTemplate $template, Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $checklist = $card->checklists()->create([
            'name' => $template->name,
            'position' => PositionService::getNextPosition($card->checklists()),
        ]);

        foreach ($template->items as $index => $itemContent) {
            $checklist->items()->create([
                'content' => $itemContent,
                'is_checked' => false,
                'position' => ($index + 1) * PositionService::GAP,
            ]);
        }

        return response()->json($checklist->load('items'), 201);
    }

    public function destroy(ChecklistTemplate $template)
    {
        abort_unless($template->board->canEdit(auth()->user()), 403);
        $template->delete();
        return response()->json(['success' => true]);
    }
}
