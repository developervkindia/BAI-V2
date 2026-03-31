<?php

namespace App\Http\Controllers\Api;

use App\Events\BoardEvent;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;
use App\Models\CardWatcher;
use App\Models\Label;
use App\Services\NotificationService;
use App\Services\PositionService;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function store(Request $request, BoardList $list)
    {
        abort_unless($list->board->canEdit(auth()->user()), 403);

        $validated = $request->validate(['title' => 'required|string|max:500']);

        $card = Card::create([
            'board_list_id' => $list->id,
            'board_id' => $list->board_id,
            'title' => $validated['title'],
            'position' => PositionService::getNextPosition($list->cards()),
            'created_by' => auth()->id(),
        ]);

        Activity::create([
            'user_id' => auth()->id(),
            'board_id' => $list->board_id,
            'card_id' => $card->id,
            'subject_type' => Card::class,
            'subject_id' => $card->id,
            'action' => 'created',
            'created_at' => now(),
        ]);

        broadcast(new BoardEvent($list->board_id, 'card.created', [
            'card' => $card->load('labels', 'members', 'checklists.items'),
            'list_id' => $list->id,
        ]))->toOthers();

        return response()->json($card->load('labels', 'members', 'checklists.items'), 201);
    }

    public function show(Card $card)
    {
        abort_unless($card->board->canAccess(auth()->user()), 403);

        $card->load([
            'boardList',
            'members',
            'labels',
            'checklists.items',
            'comments.user',
            'attachments',
            'activities' => fn($q) => $q->with('user')->latest('created_at')->limit(20),
            'creator',
            'watchers',
            'votes',
            'customFieldValues.customField',
            'dependencies.dependsOnCard:id,title',
            'dependents.card:id,title',
        ]);

        return response()->json($card);
    }

    public function update(Request $request, Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:500',
            'description' => 'nullable|string|max:10000',
            'due_date' => 'nullable|date',
            'due_date_complete' => 'sometimes|boolean',
            'due_reminder' => 'nullable|in:none,at_time,5min,1hour,1day',
            'start_date' => 'nullable|date',
            'cover_color' => 'nullable|string|max:7',
            'cover_image_path' => 'nullable|string|max:500',
        ]);

        $card->update($validated);

        return response()->json($card->fresh(['labels', 'members', 'checklists.items']));
    }

    public function destroy(Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);
        $card->delete();
        return response()->json(['success' => true]);
    }

    public function archive(Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);
        $card->update(['is_archived' => true]);
        return response()->json(['success' => true]);
    }

    public function restore(Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);
        $card->update(['is_archived' => false]);
        return response()->json($card->load('labels', 'members', 'checklists.items'));
    }

    public function move(Request $request, Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'board_list_id' => 'required|integer|exists:board_lists,id',
            'position' => 'required|numeric',
        ]);

        $fromListId = $card->board_list_id;
        $fromList = $card->boardList;
        $toList = BoardList::findOrFail($validated['board_list_id']);

        $card->update([
            'board_list_id' => $validated['board_list_id'],
            'position' => $validated['position'],
        ]);

        if ($fromListId !== (int)$validated['board_list_id']) {
            Activity::create([
                'user_id' => auth()->id(),
                'board_id' => $card->board_id,
                'card_id' => $card->id,
                'subject_type' => Card::class,
                'subject_id' => $card->id,
                'action' => 'moved',
                'data' => [
                    'from_list' => $fromList->name,
                    'to_list' => $toList->name,
                ],
                'created_at' => now(),
            ]);
        }

        broadcast(new BoardEvent($card->board_id, 'card.moved', [
            'card_id' => $card->id,
            'from_list_id' => $fromListId,
            'to_list_id' => (int)$validated['board_list_id'],
            'position' => $validated['position'],
        ]))->toOthers();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, BoardList $list)
    {
        abort_unless($list->board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:cards,id',
            'items.*.position' => 'required|numeric',
        ]);

        foreach ($validated['items'] as $item) {
            Card::where('id', $item['id'])->update([
                'board_list_id' => $list->id,
                'position' => $item['position'],
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function duplicate(Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $newCard = $card->replicate(['is_template', 'mirrored_from_card_id']);
        $newCard->title = $card->title . ' (copy)';
        $newCard->position = PositionService::getNextPosition(
            Card::where('board_list_id', $card->board_list_id)
        );
        $newCard->created_by = auth()->id();
        $newCard->save();

        // Copy labels
        $newCard->labels()->sync($card->labels->pluck('id'));

        // Copy members
        $newCard->members()->sync($card->members->pluck('id'));

        // Copy checklists with items
        foreach ($card->checklists as $checklist) {
            $newChecklist = $newCard->checklists()->create([
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

        // Copy custom field values
        foreach ($card->customFieldValues as $cfv) {
            $newCard->customFieldValues()->create([
                'custom_field_id' => $cfv->custom_field_id,
                'value' => $cfv->value,
            ]);
        }

        return response()->json(
            $newCard->load('labels', 'members', 'checklists.items', 'customFieldValues'),
            201
        );
    }

    public function toggleWatch(Card $card)
    {
        abort_unless($card->board->canAccess(auth()->user()), 403);

        $userId = auth()->id();
        $existing = CardWatcher::where('card_id', $card->id)->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['watching' => false]);
        }

        CardWatcher::create(['card_id' => $card->id, 'user_id' => $userId, 'created_at' => now()]);
        return response()->json(['watching' => true]);
    }

    public function copyToBoard(Request $request, Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'board_list_id' => 'required|integer|exists:board_lists,id',
        ]);

        $targetList = BoardList::findOrFail($validated['board_list_id']);
        abort_unless($targetList->board->canEdit(auth()->user()), 403);

        $newCard = $card->replicate(['is_template', 'mirrored_from_card_id']);
        $newCard->board_list_id = $targetList->id;
        $newCard->board_id = $targetList->board_id;
        $newCard->position = PositionService::getNextPosition(
            Card::where('board_list_id', $targetList->id)
        );
        $newCard->created_by = auth()->id();
        $newCard->save();

        // Copy labels that exist on target board (match by color+name)
        foreach ($card->labels as $label) {
            $targetLabel = Label::where('board_id', $targetList->board_id)
                ->where('color', $label->color)->first();
            if ($targetLabel) {
                $newCard->labels()->attach($targetLabel->id);
            }
        }

        // Copy checklists
        foreach ($card->checklists as $checklist) {
            $newChecklist = $newCard->checklists()->create([
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

        return response()->json($newCard->load('labels', 'members', 'checklists.items'), 201);
    }

    public function moveToBoard(Request $request, Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'board_list_id' => 'required|integer|exists:board_lists,id',
        ]);

        $targetList = BoardList::findOrFail($validated['board_list_id']);
        abort_unless($targetList->board->canEdit(auth()->user()), 403);

        $card->update([
            'board_list_id' => $targetList->id,
            'board_id' => $targetList->board_id,
            'position' => PositionService::getNextPosition(
                Card::where('board_list_id', $targetList->id)
            ),
        ]);

        // Remove labels not on target board
        $card->labels()->detach();
        $card->members()->detach();

        return response()->json($card->fresh('labels', 'members', 'checklists.items'));
    }
}
