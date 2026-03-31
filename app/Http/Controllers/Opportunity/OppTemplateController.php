<?php

namespace App\Http\Controllers\Opportunity;

use App\Http\Controllers\Controller;
use App\Models\OppProject;
use App\Models\OppSection;
use App\Models\OppTask;
use Illuminate\Http\Request;

class OppTemplateController extends Controller
{
    public function index()
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();

        $templates = OppProject::where('organization_id', $org->id)
            ->where('is_template', true)
            ->withCount(['sections', 'tasks'])
            ->with('owner')
            ->orderBy('name')
            ->get();

        return view('opportunity.templates.index', compact('templates', 'org'));
    }

    public function saveAsTemplate(OppProject $project)
    {
        abort_unless(auth()->check(), 401);

        $user = auth()->user();
        $org = $user->currentOrganization();

        $template = OppProject::create([
            'organization_id' => $org->id,
            'owner_id'        => $user->id,
            'name'            => $project->name . ' (Template)',
            'description'     => $project->description,
            'color'           => $project->color,
            'icon'            => $project->icon,
            'is_template'     => true,
            'template_id'     => $project->id,
        ]);

        // Clone sections and tasks (without dates/assignees)
        foreach ($project->sections as $section) {
            $newSection = OppSection::create([
                'project_id' => $template->id,
                'name'       => $section->name,
                'position'   => $section->position,
            ]);

            foreach ($section->tasks()->whereNull('parent_task_id')->get() as $task) {
                $this->cloneTaskAsTemplate($task, $template->id, $newSection->id);
            }
        }

        return redirect()->route('opportunity.templates.index')
            ->with('success', 'Template saved successfully.');
    }

    public function createFromTemplate(Request $request, OppProject $project)
    {
        abort_unless(auth()->check(), 401);
        abort_unless($project->is_template, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $org = $user->currentOrganization();

        $newProject = OppProject::create([
            'organization_id' => $org->id,
            'owner_id'        => $user->id,
            'name'            => $validated['name'],
            'description'     => $project->description,
            'color'           => $project->color,
            'icon'            => $project->icon,
            'is_template'     => false,
            'template_id'     => $project->id,
        ]);

        // Clone template sections and tasks into real project
        foreach ($project->sections as $section) {
            $newSection = OppSection::create([
                'project_id' => $newProject->id,
                'name'       => $section->name,
                'position'   => $section->position,
            ]);

            foreach ($section->tasks()->whereNull('parent_task_id')->get() as $task) {
                $this->cloneTaskForProject($task, $newProject->id, $newSection->id);
            }
        }

        return redirect()->route('opportunity.projects.show', $newProject)
            ->with('success', 'Project created from template.');
    }

    /**
     * Clone a task as a template entry (no dates, no assignees).
     */
    private function cloneTaskAsTemplate(OppTask $task, int $projectId, int $sectionId, ?int $parentId = null): void
    {
        $newTask = OppTask::create([
            'project_id'     => $projectId,
            'section_id'     => $sectionId,
            'parent_task_id' => $parentId,
            'title'          => $task->title,
            'description'    => $task->description,
            'position'       => $task->position,
            'status'         => 'incomplete',
        ]);

        foreach ($task->children as $child) {
            $this->cloneTaskAsTemplate($child, $projectId, $sectionId, $newTask->id);
        }
    }

    /**
     * Clone a task for a real project (no dates, no assignees).
     */
    private function cloneTaskForProject(OppTask $task, int $projectId, int $sectionId, ?int $parentId = null): void
    {
        $newTask = OppTask::create([
            'project_id'     => $projectId,
            'section_id'     => $sectionId,
            'parent_task_id' => $parentId,
            'title'          => $task->title,
            'description'    => $task->description,
            'position'       => $task->position,
            'status'         => 'incomplete',
        ]);

        foreach ($task->children as $child) {
            $this->cloneTaskForProject($child, $projectId, $sectionId, $newTask->id);
        }
    }
}
