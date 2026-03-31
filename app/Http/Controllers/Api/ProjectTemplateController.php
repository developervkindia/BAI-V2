<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectTemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless($user->currentOrganization()?->id, 403);

        $templates = ProjectTemplate::where('organization_id', $user->currentOrganization()?->id)
            ->latest()
            ->get();

        return response()->json($templates);
    }

    public function saveAsTemplate(Request $request, Project $project)
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $structure = [
            'taskLists' => $project->taskLists()->with('tasks')->get()->map(function ($list) {
                return [
                    'name' => $list->name,
                    'position' => $list->position,
                    'tasks' => $list->tasks->map(function ($task) {
                        return [
                            'title' => $task->title,
                            'description' => $task->description,
                            'priority' => $task->priority,
                            'position' => $task->position,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
            'labels' => $project->labels()->get()->map(function ($label) {
                return [
                    'name' => $label->name,
                    'color' => $label->color,
                ];
            })->toArray(),
            'milestones' => $project->milestones()->get()->map(function ($milestone) {
                return [
                    'name' => $milestone->name,
                    'description' => $milestone->description,
                ];
            })->toArray(),
        ];

        $template = ProjectTemplate::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'organization_id' => $project->organization_id,
            'project_id' => $project->id,
            'structure' => $structure,
            'created_by' => auth()->id(),
        ]);

        return response()->json($template, 201);
    }

    public function createFromTemplate(Request $request, ProjectTemplate $template)
    {
        $user = auth()->user();
        abort_unless($template->organization_id === $user->currentOrganization()?->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prefix' => 'nullable|string|max:10',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'prefix' => $validated['prefix'] ?? strtoupper(Str::substr($validated['name'], 0, 3)),
            'organization_id' => $template->organization_id,
            'owner_id' => $user->id,
        ]);

        $structure = $template->structure;

        // Create labels
        $labelMap = [];
        if (!empty($structure['labels'])) {
            foreach ($structure['labels'] as $labelData) {
                $label = $project->labels()->create($labelData);
                $labelMap[$labelData['name']] = $label->id;
            }
        }

        // Create milestones
        if (!empty($structure['milestones'])) {
            foreach ($structure['milestones'] as $milestoneData) {
                $project->milestones()->create($milestoneData);
            }
        }

        // Create task lists and tasks
        if (!empty($structure['taskLists'])) {
            foreach ($structure['taskLists'] as $listData) {
                $tasks = $listData['tasks'] ?? [];
                unset($listData['tasks']);

                $list = $project->taskLists()->create($listData);

                foreach ($tasks as $taskData) {
                    $list->tasks()->create(array_merge($taskData, [
                        'project_id' => $project->id,
                    ]));
                }
            }
        }

        return response()->json($project->load(['taskLists', 'labels', 'milestones']), 201);
    }

    public function clone(Request $request, Project $project)
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => 'nullable|string|max:10',
        ]);

        $newProject = Project::create([
            'name' => $validated['name'],
            'description' => $project->description,
            'prefix' => $validated['prefix'] ?? strtoupper(Str::substr($validated['name'], 0, 3)),
            'organization_id' => $project->organization_id,
            'owner_id' => auth()->id(),
        ]);

        // Clone labels
        $labelMap = [];
        foreach ($project->labels as $label) {
            $newLabel = $newProject->labels()->create([
                'name' => $label->name,
                'color' => $label->color,
            ]);
            $labelMap[$label->id] = $newLabel->id;
        }

        // Clone milestones
        $milestoneMap = [];
        foreach ($project->milestones as $milestone) {
            $newMilestone = $newProject->milestones()->create([
                'name' => $milestone->name,
                'description' => $milestone->description,
            ]);
            $milestoneMap[$milestone->id] = $newMilestone->id;
        }

        // Clone task lists and tasks
        foreach ($project->taskLists as $list) {
            $newList = $newProject->taskLists()->create([
                'name' => $list->name,
                'position' => $list->position,
            ]);

            foreach ($list->tasks as $task) {
                $newTask = $newList->tasks()->create([
                    'project_id' => $newProject->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'priority' => $task->priority,
                    'position' => $task->position,
                    'milestone_id' => isset($milestoneMap[$task->milestone_id]) ? $milestoneMap[$task->milestone_id] : null,
                ]);

                // Clone task labels
                foreach ($task->labels as $label) {
                    if (isset($labelMap[$label->id])) {
                        $newTask->labels()->attach($labelMap[$label->id]);
                    }
                }
            }
        }

        return response()->json($newProject->load(['taskLists.tasks', 'labels', 'milestones']), 201);
    }

    public function destroy(ProjectTemplate $template)
    {
        $user = auth()->user();
        abort_unless($template->organization_id === $user->currentOrganization()?->id, 403);

        $template->delete();

        return response()->json(['message' => 'Template deleted.']);
    }
}
