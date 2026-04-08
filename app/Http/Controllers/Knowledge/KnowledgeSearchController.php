<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeCategory;
use App\Models\Organization;
use App\Services\KnowledgeSearchService;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeSearchController extends Controller
{
    public function __construct(
        protected KnowledgeSearchService $searchService,
        protected PlanService $planService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', KnowledgeCategory::class);

        $org = $request->user()->currentOrganization();
        abort_unless($org instanceof Organization, 404);

        if (! $this->planService->canUse($org, 'knowledge_base', 'fulltext_search') && ! $request->user()->is_super_admin) {
            abort(403, 'Search is not available on your plan.');
        }

        $q = (string) $request->query('q', '');
        $results = $this->searchService->search(
            $request->user(),
            $org->id,
            $q,
            includeDraftsForContributor: true
        );

        return view('knowledge.search', [
            'query' => $q,
            'results' => $results,
        ]);
    }
}
