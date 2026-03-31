<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\RecurringTaskPattern;
use Illuminate\Http\Request;

class RecurringTaskController extends Controller
{
    public function index(Project $project)
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $patterns = $project->recurringTaskPatterns()
            ->latest()
            ->get();

        return response()->json($patterns);
    }

    public function store(Request $request, Project $project)
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|string|in:daily,weekly,biweekly,monthly,quarterly,yearly,custom',
            'interval' => 'nullable|integer|min:1',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'month_of_year' => 'nullable|integer|min:1|max:12',
            'cron_expression' => 'nullable|string|max:100',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'project_task_list_id' => 'nullable|exists:project_task_lists,id',
            'assignee_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $pattern = $project->recurringTaskPatterns()->create(array_merge($validated, [
            'created_by' => auth()->id(),
        ]));

        return response()->json($pattern, 201);
    }

    public function update(Request $request, RecurringTaskPattern $pattern)
    {
        abort_unless($pattern->project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'sometimes|required|string|in:daily,weekly,biweekly,monthly,quarterly,yearly,custom',
            'interval' => 'nullable|integer|min:1',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'month_of_year' => 'nullable|integer|min:1|max:12',
            'cron_expression' => 'nullable|string|max:100',
            'starts_at' => 'sometimes|required|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'project_task_list_id' => 'nullable|exists:project_task_lists,id',
            'assignee_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $pattern->update($validated);

        return response()->json($pattern);
    }

    public function destroy(RecurringTaskPattern $pattern)
    {
        abort_unless($pattern->project->canEdit(auth()->user()), 403);

        $pattern->delete();

        return response()->json(['message' => 'Recurring task pattern deleted.']);
    }

    public function toggleActive(RecurringTaskPattern $pattern)
    {
        abort_unless($pattern->project->canEdit(auth()->user()), 403);

        $pattern->update(['is_active' => !$pattern->is_active]);

        return response()->json($pattern);
    }
}
