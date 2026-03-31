<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectTask;
use App\Models\ProjectTaskChecklist;
use App\Models\ProjectTaskChecklistItem;
use Illuminate\Http\Request;

class ProjectTaskChecklistController extends Controller
{
    public function store(Request $request, ProjectTask $task)
    {
        abort_unless($task->project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $position = $task->checklists()->max('position') + 1;

        $checklist = $task->checklists()->create([
            'name' => $validated['name'],
            'position' => $position,
        ]);

        return response()->json($checklist, 201);
    }

    public function update(Request $request, ProjectTaskChecklist $checklist)
    {
        abort_unless($checklist->task->project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $checklist->update($validated);

        return response()->json($checklist);
    }

    public function destroy(ProjectTaskChecklist $checklist)
    {
        abort_unless($checklist->task->project->canEdit(auth()->user()), 403);

        $checklist->delete();

        return response()->json(['message' => 'Checklist deleted.']);
    }

    public function storeItem(Request $request, ProjectTaskChecklist $checklist)
    {
        abort_unless($checklist->task->project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $position = $checklist->items()->max('position') + 1;

        $item = $checklist->items()->create([
            'content' => $validated['content'],
            'is_checked' => false,
            'position' => $position,
        ]);

        return response()->json($item, 201);
    }

    public function updateItem(Request $request, ProjectTaskChecklistItem $item)
    {
        abort_unless($item->checklist->task->project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'content' => 'sometimes|required|string|max:1000',
            'is_checked' => 'sometimes|boolean',
        ]);

        $item->update($validated);

        return response()->json($item);
    }

    public function toggleItem(ProjectTaskChecklistItem $item)
    {
        abort_unless($item->checklist->task->project->canEdit(auth()->user()), 403);

        $item->update(['is_checked' => !$item->is_checked]);

        return response()->json($item);
    }

    public function destroyItem(ProjectTaskChecklistItem $item)
    {
        abort_unless($item->checklist->task->project->canEdit(auth()->user()), 403);

        $item->delete();

        return response()->json(['message' => 'Checklist item deleted.']);
    }
}
