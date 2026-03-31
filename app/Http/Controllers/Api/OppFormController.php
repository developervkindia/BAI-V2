<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OppForm;
use App\Models\OppFormSubmission;
use App\Models\OppProject;
use App\Models\OppSection;
use App\Models\OppTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OppFormController extends Controller
{
    public function index(OppProject $project): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $forms = OppForm::where('project_id', $project->id)
            ->with('creator')
            ->withCount('submissions')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['forms' => $forms]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'project_id' => 'required|exists:opp_projects,id',
            'fields'     => 'required|array',
            'is_public'  => 'boolean',
        ]);

        $form = OppForm::create(array_merge($validated, [
            'created_by' => auth()->id(),
        ]));

        return response()->json(['form' => $form->load('creator')], 201);
    }

    public function update(Request $request, OppForm $form): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:255',
            'fields'    => 'sometimes|required|array',
            'is_active' => 'sometimes|boolean',
            'is_public' => 'sometimes|boolean',
        ]);

        $form->update($validated);

        return response()->json(['form' => $form->fresh('creator')]);
    }

    public function destroy(OppForm $form): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $form->delete();

        return response()->json(['message' => 'Form deleted.']);
    }

    public function showPublic(string $slug)
    {
        $form = OppForm::where('slug', $slug)->firstOrFail();

        abort_unless($form->is_active && $form->is_public, 404);

        return view('opportunity.forms.public', compact('form'));
    }

    public function submit(Request $request, string $slug)
    {
        $form = OppForm::where('slug', $slug)->firstOrFail();

        abort_unless($form->is_active && $form->is_public, 404);

        // Build validation rules from form fields config
        $rules = [];
        foreach ($form->fields as $field) {
            $fieldKey = 'data.' . $field['name'];
            $fieldRules = [];

            if (!empty($field['required'])) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            if (!empty($field['type'])) {
                match ($field['type']) {
                    'email'  => $fieldRules[] = 'email',
                    'number' => $fieldRules[] = 'numeric',
                    'url'    => $fieldRules[] = 'url',
                    default  => $fieldRules[] = 'string',
                };
            } else {
                $fieldRules[] = 'string';
            }

            $rules[$fieldKey] = $fieldRules;
        }

        $validated = $request->validate($rules);

        $submission = OppFormSubmission::create([
            'form_id'            => $form->id,
            'data'               => $validated['data'] ?? [],
            'submitted_by_name'  => $request->input('name'),
            'submitted_by_email' => $request->input('email'),
            'created_at'         => now(),
        ]);

        // Auto-create task if submit_action is configured
        if (!empty($form->submit_action['create_task'])) {
            $actionConfig = $form->submit_action;

            $sectionId = $actionConfig['section_id'] ?? null;
            if (!$sectionId) {
                $sectionId = OppSection::where('project_id', $form->project_id)
                    ->orderBy('position')
                    ->value('id');
            }

            $task = OppTask::create([
                'project_id'  => $form->project_id,
                'section_id'  => $sectionId,
                'title'       => $actionConfig['task_title'] ?? ('Form submission: ' . $form->name),
                'description' => collect($validated['data'] ?? [])->map(fn ($v, $k) => "$k: $v")->implode("\n"),
                'status'      => 'incomplete',
                'position'    => 0,
                'created_by'  => $form->created_by,
            ]);

            $submission->update(['task_id' => $task->id]);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Form submitted successfully.', 'submission' => $submission]);
        }

        return redirect()->back()->with('success', 'Form submitted successfully.');
    }
}
