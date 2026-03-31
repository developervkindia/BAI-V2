<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Card;
use App\Models\BoardList;
use App\Services\PositionService;
use Illuminate\Http\Request;

class CardTemplateController extends Controller
{
    public function index(Board $board)
    {
        abort_unless($board->canAccess(auth()->user()), 403);

        $templates = $board->cardTemplates()
            ->with('labels', 'checklists.items')
            ->get();

        return response()->json($templates);
    }

    public function store(Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $template = $card->replicate(['position', 'board_list_id', 'mirrored_from_card_id']);
        $template->is_template = true;
        $template->is_archived = false;
        $template->position = 0;
        $template->board_list_id = $card->board_list_id;
        $template->created_by = auth()->id();
        $template->save();

        // Copy labels
        $template->labels()->sync($card->labels->pluck('id'));

        // Copy checklists
        foreach ($card->checklists as $checklist) {
            $newChecklist = $template->checklists()->create([
                'name' => $checklist->name,
                'position' => $checklist->position,
            ]);
            foreach ($checklist->items as $item) {
                $newChecklist->items()->create([
                    'content' => $item->content,
                    'is_checked' => false,
                    'position' => $item->position,
                ]);
            }
        }

        return response()->json($template->load('labels', 'checklists.items'), 201);
    }

    public function createFromTemplate(Request $request, Board $board)
    {
        abort_unless($board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'template_id' => 'required|integer|exists:cards,id',
            'board_list_id' => 'required|integer|exists:board_lists,id',
            'title' => 'nullable|string|max:500',
        ]);

        $template = Card::where('id', $validated['template_id'])
            ->where('is_template', true)
            ->firstOrFail();

        $list = BoardList::findOrFail($validated['board_list_id']);

        $card = $template->replicate(['is_template', 'mirrored_from_card_id']);
        $card->is_template = false;
        $card->board_list_id = $list->id;
        $card->board_id = $board->id;
        $card->title = $validated['title'] ?? $template->title;
        $card->position = PositionService::getNextPosition(Card::where('board_list_id', $list->id));
        $card->created_by = auth()->id();
        $card->save();

        // Copy labels
        $card->labels()->sync($template->labels->pluck('id'));

        // Copy checklists
        foreach ($template->checklists as $checklist) {
            $newChecklist = $card->checklists()->create([
                'name' => $checklist->name,
                'position' => $checklist->position,
            ]);
            foreach ($checklist->items as $item) {
                $newChecklist->items()->create([
                    'content' => $item->content,
                    'is_checked' => false,
                    'position' => $item->position,
                ]);
            }
        }

        return response()->json($card->load('labels', 'members', 'checklists.items'), 201);
    }

    public function destroy(Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);
        abort_unless($card->is_template, 404);

        $card->forceDelete();
        return response()->json(['success' => true]);
    }
}
