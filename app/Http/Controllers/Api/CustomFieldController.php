<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Card;
use App\Models\CardCustomFieldValue;
use App\Models\CustomField;
use App\Services\PositionService;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    public function index(Board $board)
    {
        abort_unless($board->canAccess(auth()->user()), 403);
        return response()->json($board->customFields);
    }

    public function store(Request $request, Board $board)
    {
        abort_unless($board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,dropdown,date,checkbox,number',
            'options' => 'nullable|array',
            'options.*' => 'string|max:255',
        ]);

        $field = CustomField::create([
            'board_id' => $board->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'options' => $validated['options'] ?? null,
            'position' => PositionService::getNextPosition($board->customFields()),
        ]);

        return response()->json($field, 201);
    }

    public function update(Request $request, CustomField $field)
    {
        abort_unless($field->board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'options' => 'nullable|array',
            'options.*' => 'string|max:255',
        ]);

        $field->update($validated);
        return response()->json($field);
    }

    public function destroy(CustomField $field)
    {
        abort_unless($field->board->canEdit(auth()->user()), 403);
        $field->values()->delete();
        $field->delete();
        return response()->json(['success' => true]);
    }

    public function updateCardValue(Request $request, Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'custom_field_id' => 'required|integer|exists:custom_fields,id',
            'value' => 'nullable|string|max:5000',
        ]);

        CardCustomFieldValue::updateOrCreate(
            ['card_id' => $card->id, 'custom_field_id' => $validated['custom_field_id']],
            ['value' => $validated['value']]
        );

        return response()->json($card->fresh('customFieldValues'));
    }
}
