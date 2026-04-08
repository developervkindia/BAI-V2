<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Services\KnowledgeQuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KnowledgeCategoryController extends Controller
{
    public function __construct(
        protected KnowledgeQuotaService $quota,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('create', KnowledgeCategory::class);

        $org = $request->user()->currentOrganization();
        $categories = KnowledgeCategory::where('organization_id', $org->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount('articles')
            ->get();

        return view('knowledge.categories.index', compact('categories'));
    }

    public function show(Request $request, KnowledgeCategory $knowledge_category): View
    {
        $this->authorize('view', $knowledge_category);

        $sort = $request->query('sort', 'updated');
        $articles = KnowledgeArticle::where('knowledge_category_id', $knowledge_category->id)
            ->where('organization_id', $knowledge_category->organization_id)
            ->with(['author', 'tags'])
            ->get()
            ->filter(fn (KnowledgeArticle $a) => $request->user()->can('view', $a))
            ->values();

        if ($sort === 'title') {
            $articles = $articles->sortBy('title');
        } else {
            $articles = $articles->sortByDesc('updated_at');
        }

        return view('knowledge.categories.show', [
            'category' => $knowledge_category,
            'articles' => $articles,
            'sort' => $sort,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', KnowledgeCategory::class);
        abort_unless($this->quota->canCreateCategory($request->user()->currentOrganization()), 403, 'Category limit reached for your plan.');

        return view('knowledge.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', KnowledgeCategory::class);
        $org = $request->user()->currentOrganization();
        abort_unless($this->quota->canCreateCategory($org), 403, 'Category limit reached for your plan.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:64'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
        ]);

        $category = new KnowledgeCategory($data);
        $category->organization_id = $org->id;
        $category->slug = KnowledgeCategory::uniqueSlugForOrg($org->id, $data['name']);
        $category->sort_order = $data['sort_order'] ?? 0;
        $category->save();

        return redirect()->route('knowledge.categories.show', $category)
            ->with('success', 'Category created.');
    }

    public function edit(KnowledgeCategory $knowledge_category): View
    {
        $this->authorize('update', $knowledge_category);

        return view('knowledge.categories.edit', ['category' => $knowledge_category]);
    }

    public function update(Request $request, KnowledgeCategory $knowledge_category): RedirectResponse
    {
        $this->authorize('update', $knowledge_category);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('knowledge_categories', 'slug')->where('organization_id', $knowledge_category->organization_id)->ignore($knowledge_category->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:64'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = KnowledgeCategory::uniqueSlugForOrg($knowledge_category->organization_id, $data['name'], $knowledge_category->id);
        }

        $knowledge_category->update($data);

        return redirect()->route('knowledge.categories.show', $knowledge_category)
            ->with('success', 'Category updated.');
    }

    public function destroy(KnowledgeCategory $knowledge_category): RedirectResponse
    {
        $this->authorize('delete', $knowledge_category);

        if ($knowledge_category->articles()->exists()) {
            return back()->with('error', 'Move or delete articles in this category first.');
        }

        $knowledge_category->delete();

        return redirect()->route('knowledge.index')->with('success', 'Category deleted.');
    }
}
