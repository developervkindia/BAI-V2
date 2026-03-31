<?php

namespace App\Http\Controllers\Opportunity;

use App\Http\Controllers\Controller;
use App\Models\OppPortfolio;
use App\Models\OppProject;
use Illuminate\Http\Request;

class OppPortfolioController extends Controller
{
    public function index()
    {
        abort_unless(auth()->check(), 401);

        $user = auth()->user();
        $org = $user->currentOrganization();

        $portfolios = OppPortfolio::where('organization_id', $org->id)
            ->withCount('projects')
            ->with('owner')
            ->orderBy('name')
            ->get();

        return view('opportunity.portfolios.index', compact('portfolios', 'org'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $user = auth()->user();
        $org = $user->currentOrganization();

        $portfolio = OppPortfolio::create(array_merge($validated, [
            'organization_id' => $org->id,
            'owner_id'        => $user->id,
        ]));

        if ($request->wantsJson()) {
            return response()->json(['portfolio' => $portfolio->load('owner')], 201);
        }

        return redirect()->route('opportunity.portfolios.index')
            ->with('success', 'Portfolio created successfully.');
    }

    public function show(OppPortfolio $portfolio)
    {
        abort_unless(auth()->check(), 401);

        $portfolio->load([
            'projects' => function ($query) {
                $query->withCount([
                    'tasks',
                    'tasks as completed_tasks_count' => fn ($q) => $q->where('status', 'complete'),
                ]);
            },
        ]);

        return view('opportunity.portfolios.show', compact('portfolio'));
    }

    public function addProject(Request $request, OppPortfolio $portfolio)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'project_id' => 'required|exists:opp_projects,id',
        ]);

        $portfolio->projects()->syncWithoutDetaching([$validated['project_id']]);

        return response()->json(['message' => 'Project added to portfolio.']);
    }

    public function removeProject(OppPortfolio $portfolio, OppProject $project)
    {
        abort_unless(auth()->check(), 401);

        $portfolio->projects()->detach($project->id);

        return response()->json(['message' => 'Project removed from portfolio.']);
    }

    public function destroy(OppPortfolio $portfolio)
    {
        abort_unless(auth()->check(), 401);

        $portfolio->delete();

        return redirect()->route('opportunity.portfolios.index')
            ->with('success', 'Portfolio deleted successfully.');
    }
}
