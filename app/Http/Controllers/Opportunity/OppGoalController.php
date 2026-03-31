<?php

namespace App\Http\Controllers\Opportunity;

use App\Http\Controllers\Controller;
use App\Models\OppGoal;
use App\Models\OppGoalLink;
use Illuminate\Http\Request;

class OppGoalController extends Controller
{
    public function index()
    {
        abort_unless(auth()->check(), 401);

        $user = auth()->user();
        $org = $user->currentOrganization();

        $goals = OppGoal::where('organization_id', $org->id)
            ->with('owner')
            ->withCount('children')
            ->orderBy('goal_type')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('goal_type');

        return view('opportunity.goals.index', compact('goals', 'org'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'goal_type'    => 'required|string|max:50',
            'metric_type'  => 'nullable|string|max:50',
            'target_value' => 'nullable|numeric|min:0',
            'start_date'   => 'nullable|date',
            'due_date'     => 'nullable|date|after_or_equal:start_date',
            'parent_id'    => 'nullable|exists:opp_goals,id',
        ]);

        $user = auth()->user();
        $org = $user->currentOrganization();

        $goal = OppGoal::create(array_merge($validated, [
            'organization_id' => $org->id,
            'owner_id'        => $user->id,
        ]));

        if ($request->wantsJson()) {
            return response()->json(['goal' => $goal->load('owner')], 201);
        }

        return redirect()->route('opportunity.goals.index')
            ->with('success', 'Goal created successfully.');
    }

    public function show(OppGoal $goal)
    {
        abort_unless(auth()->check(), 401);

        $goal->load(['owner', 'children', 'links.linkable']);

        return view('opportunity.goals.show', compact('goal'));
    }

    public function update(Request $request, OppGoal $goal)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'title'        => 'sometimes|required|string|max:255',
            'description'  => 'nullable|string',
            'status'       => 'sometimes|required|string|max:50',
            'current_value' => 'nullable|numeric|min:0',
            'target_value' => 'nullable|numeric|min:0',
        ]);

        $goal->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['goal' => $goal->fresh('owner')]);
        }

        return back()->with('success', 'Goal updated successfully.');
    }

    public function destroy(OppGoal $goal)
    {
        abort_unless(auth()->check(), 401);

        $goal->delete();

        return redirect()->route('opportunity.goals.index')
            ->with('success', 'Goal deleted successfully.');
    }

    public function updateProgress(Request $request, OppGoal $goal)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'current_value' => 'required|numeric|min:0',
        ]);

        $goal->current_value = $validated['current_value'];

        if ($goal->target_value > 0 && $goal->current_value >= $goal->target_value) {
            $goal->status = 'achieved';
        }

        $goal->save();

        return response()->json(['goal' => $goal->fresh('owner')]);
    }

    public function linkItem(Request $request, OppGoal $goal)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'linkable_type' => 'required|string',
            'linkable_id'   => 'required|integer',
        ]);

        $link = OppGoalLink::create([
            'goal_id'       => $goal->id,
            'linkable_type' => $validated['linkable_type'],
            'linkable_id'   => $validated['linkable_id'],
        ]);

        return response()->json(['link' => $link->load('linkable')], 201);
    }

    public function unlinkItem(OppGoalLink $link)
    {
        abort_unless(auth()->check(), 401);

        $link->delete();

        return response()->json(['message' => 'Link removed.']);
    }
}
