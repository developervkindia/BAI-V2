<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class KnowledgeHomeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', KnowledgeCategory::class);

        $org = $request->user()->currentOrganization();
        abort_unless($org, 404);

        $categories = KnowledgeCategory::where('organization_id', $org->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount('articles')
            ->get();

        $popularQuery = KnowledgeArticle::where('organization_id', $org->id)
            ->with(['category', 'author', 'tags']);

        $popularQuery->where(function ($q) use ($request) {
            $q->where('status', 'published');
            $user = $request->user();

            if ($user->is_super_admin) {
                $q->orWhere('status', 'draft');

                return;
            }

            if (Gate::forUser($user)->allows('create', KnowledgeCategory::class)) {
                $q->orWhere('status', 'draft');

                return;
            }

            if (Gate::forUser($user)->allows('create', KnowledgeArticle::class)) {
                $q->orWhere(function ($inner) use ($user) {
                    $inner->where('status', 'draft')->where('author_id', $user->id);
                });
            }
        });

        if ($request->filled('category')) {
            $popularQuery->whereHas('category', fn ($q) => $q->where('slug', $request->query('category')));
        }

        if ($request->filled('tag')) {
            $popularQuery->whereHas('tags', fn ($q) => $q->where('slug', $request->query('tag')));
        }

        $popularArticles = $popularQuery
            ->orderByDesc('pinned')
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get();

        $allTags = KnowledgeTag::where('organization_id', $org->id)
            ->whereHas('articles', fn ($q) => $q->where('status', 'published'))
            ->orderBy('name')
            ->get();

        $featuredArticle = $popularArticles->firstWhere('pinned', true) ?? $popularArticles->first();

        return view('knowledge.index', [
            'categories' => $categories,
            'popularArticles' => $popularArticles,
            'allTags' => $allTags,
            'featuredArticle' => $featuredArticle,
            'filterCategory' => $request->query('category', ''),
            'filterTag' => $request->query('tag', ''),
        ]);
    }
}
