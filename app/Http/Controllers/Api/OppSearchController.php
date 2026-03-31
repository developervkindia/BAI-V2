<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OppProject;
use App\Models\OppSavedSearch;
use App\Models\OppTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OppSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'query' => 'required|string|min:1|max:255',
        ]);

        $org = auth()->user()->currentOrganization();
        $query = '%' . $validated['query'] . '%';

        $tasks = OppTask::whereHas('project', fn ($q) => $q->where('organization_id', $org->id))
            ->where('title', 'LIKE', $query)
            ->with(['project', 'assignee', 'section'])
            ->limit(20)
            ->get();

        $projects = OppProject::where('organization_id', $org->id)
            ->where('name', 'LIKE', $query)
            ->where('is_template', false)
            ->limit(10)
            ->get();

        return response()->json([
            'tasks'    => $tasks,
            'projects' => $projects,
        ]);
    }

    public function savedSearches(): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $searches = OppSavedSearch::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['saved_searches' => $searches]);
    }

    public function saveSearch(Request $request): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'filters' => 'required|array',
        ]);

        $org = auth()->user()->currentOrganization();

        $search = OppSavedSearch::create([
            'organization_id' => $org->id,
            'user_id'         => auth()->id(),
            'name'            => $validated['name'],
            'filters'         => $validated['filters'],
        ]);

        return response()->json(['saved_search' => $search], 201);
    }

    public function deleteSavedSearch(OppSavedSearch $search): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $search->delete();

        return response()->json(['message' => 'Saved search deleted.']);
    }
}
