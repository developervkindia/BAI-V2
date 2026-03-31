<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCustomField;
use App\Models\ProjectTask;
use App\Models\ProjectTaskCustomFieldValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectCustomFieldController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $fields = $project->customFields()->orderBy('position')->get()
            ->map(fn(ProjectCustomField $f) => $this->format($f));

        return response()->json(['fields' => $fields]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->isManager(auth()->user()), 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:text,number,date,dropdown,checkbox,url',
            'options'     => 'nullable|array',
            'options.*'   => 'string|max:100',
            'is_required' => 'sometimes|boolean',
        ]);

        $maxPos = $project->customFields()->max('position') ?? 0;

        $field = $project->customFields()->create(array_merge($validated, [
            'position' => $maxPos + 1,
        ]));

        return response()->json(['success' => true, 'field' => $this->format($field)]);
    }

    public function update(Request $request, ProjectCustomField $field): JsonResponse
    {
        abort_unless($field->project->isManager(auth()->user()), 403);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:100',
            'type'        => 'sometimes|in:text,number,date,dropdown,checkbox,url',
            'options'     => 'nullable|array',
            'options.*'   => 'string|max:100',
            'is_required' => 'sometimes|boolean',
        ]);

        $field->update($validated);

        return response()->json(['success' => true, 'field' => $this->format($field)]);
    }

    public function destroy(ProjectCustomField $field): JsonResponse
    {
        abort_unless($field->project->isManager(auth()->user()), 403);

        $field->delete();

        return response()->json(['success' => true]);
    }

    public function updateTaskValue(Request $request, ProjectTask $task): JsonResponse
    {
        abort_unless($task->project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'field_id' => 'required|exists:project_custom_fields,id',
            'value'    => 'nullable|string|max:5000',
        ]);

        ProjectTaskCustomFieldValue::updateOrCreate(
            [
                'project_task_id' => $task->id,
                'custom_field_id' => $validated['field_id'],
            ],
            [
                'value' => $validated['value'],
            ]
        );

        return response()->json(['success' => true]);
    }

    private function format(ProjectCustomField $field): array
    {
        return [
            'id'          => $field->id,
            'name'        => $field->name,
            'type'        => $field->type,
            'options'     => $field->options,
            'is_required' => $field->is_required,
            'position'    => $field->position,
        ];
    }
}
