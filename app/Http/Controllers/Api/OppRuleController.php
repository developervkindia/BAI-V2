<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OppProject;
use App\Models\OppRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OppRuleController extends Controller
{
    public function index(OppProject $project): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $rules = OppRule::where('project_id', $project->id)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['rules' => $rules]);
    }

    public function store(Request $request, OppProject $project): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'trigger_type'   => 'required|string|max:100',
            'trigger_config' => 'required|array',
            'action_type'    => 'required|string|max:100',
            'action_config'  => 'required|array',
        ]);

        $rule = OppRule::create(array_merge($validated, [
            'project_id' => $project->id,
            'created_by' => auth()->id(),
        ]));

        return response()->json(['rule' => $rule->load('creator')], 201);
    }

    public function update(Request $request, OppRule $rule): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'is_active'      => 'sometimes|boolean',
            'trigger_config' => 'sometimes|required|array',
            'action_config'  => 'sometimes|required|array',
        ]);

        $rule->update($validated);

        return response()->json(['rule' => $rule->fresh('creator')]);
    }

    public function destroy(OppRule $rule): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $rule->delete();

        return response()->json(['message' => 'Rule deleted.']);
    }
}
